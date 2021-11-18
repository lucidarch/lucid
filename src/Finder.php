<?php

namespace Lucid;

use Exception;
use InvalidArgumentException;
use Illuminate\Support\Collection;
use Lucid\Entities\Feature;
use Lucid\Entities\Service;
use Lucid\Entities\Domain;
use Lucid\Entities\Job;
use Symfony\Component\Finder\Finder as SymfonyFinder;

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

trait Finder
{
    public function fuzzyFind($query)
    {
        $finder = new SymfonyFinder();

        $files = $finder->in($this->findServicesRootPath().'/*/Features') // features
            ->in($this->findDomainsRootPath().'/*/Jobs') // jobs
            ->name('*.php')
            ->files();

        $matches = [
            'jobs' => [],
            'features' => [],
        ];

        foreach ($files as $file) {
            $base = $file->getBaseName();
            $name = str_replace(['.php', ' '], '', $base);

            $query = str_replace(' ', '', trim($query));

            similar_text($query, mb_strtolower($name), $percent);

            if ($percent > 35) {
                if (strpos($base, 'Feature.php')) {
                    $matches['features'][] = [$this->findFeature($name)->toArray(), $percent];
                } elseif (strpos($base, 'Job.php')) {
                    $matches['jobs'][] = [$this->findJob($name)->toArray(), $percent];
                }
            }
        }

        // sort the results by their similarity percentage
        $this->sortFuzzyResults($matches['jobs']);
        $this->sortFuzzyResults($matches['features']);

        $matches['features'] = $this->mapFuzzyResults($matches['features']);
        $matches['jobs'] = array_map(function ($result) {
            return $result[0];
        }, $matches['jobs']);

        return $matches;
    }

    /**
     * Sort the fuzzy-find results.
     *
     * @param array &$results
     *
     * @return bool
     */
    private function sortFuzzyResults(&$results)
    {
        return usort($results, function ($resultLeft, $resultRight) {
            return $resultLeft[1] < $resultRight[1];
        });
    }

     /**
      * Map the fuzzy-find results into the data
      * that should be returned.
      *
      * @param  array $results
      *
      * @return array
      */
     private function mapFuzzyResults($results)
     {
         return array_map(function ($result) {
            return $result[0];
        }, $results);
     }

    /**
     * Get the source directory name.
     *
     * @return string
     */
    public function getSourceDirectoryName()
    {
        return 'app';
    }

    /**
     * Determines whether this is a lucid microservice installation.
     *
     * @return bool
     */
    public function isMicroservice()
    {
        return !file_exists(base_path().DS.$this->getSourceDirectoryName().DS.'Services');
    }

    /**
     * Get the namespace used for the application.
     *
     * @return string
     *
     * @throws Exception
     */
    public function findNamespace(string $dir)
    {
        // read composer.json file contents to determine the namespace
        $composer = json_decode(file_get_contents(base_path(). DS .'composer.json'), true);

        // see which one refers to the "src/" directory
        foreach ($composer['autoload']['psr-4'] as $namespace => $directory) {
            $directory = str_replace(['/', '\\'], DS, $directory);
            if ($directory === $dir.DS) {
                return trim($namespace, '\\');
            }
        }

        throw new Exception('App namespace not set in composer.json');
    }

    public function findRootNamespace()
    {
        return $this->findNamespace($this->getSourceDirectoryName());
    }

    public function findAppNamespace()
    {
        return $this->findNamespace('app');
    }

    /**
     * Find the namespace of the foundation.
     *
     * @return string
     */
    public function findFoundationNamespace()
    {
        return 'Lucid\Foundation';
    }

    /**
     * Find the namespace of a unit.
     *
     * @return string
     */
    public function findUnitNamespace()
    {
        return 'Lucid\Units';
    }

    /**
     * Find the namespace for the given service name.
     *
     * @param string $service
     *
     * @return string
     * @throws Exception
     */
    public function findServiceNamespace($service = null)
    {
        $root = $this->findRootNamespace();

        return (!$service) ? $root : "$root\\Services\\$service";
    }

    /**
     * get the root of the source directory.
     *
     * @return string
     */
    public function findSourceRoot()
    {
        return app_path();
    }

    /**
     * Find the root path of all the services.
     *
     * @return string
     */
    public function findServicesRootPath()
    {
        return $this->findSourceRoot(). DS .'Services';
    }

    /**
     * Find the path to the directory of the given service name.
     * In the case of a microservice service installation this will be app path.
     *
     * @param string $service
     *
     * @return string
     */
    public function findServicePath($service)
    {
        return (!$service) ? app_path() : $this->findServicesRootPath(). DS . $service;
    }

    /**
     * Find the path to the directory of the given service name.
     * In the case of a microservice service installation this will be app path.
     *
     * @param string $service
     *
     * @return string
     */
    public function findMigrationPath($service)
    {
        return (!$service) ?
            'database/migrations' :
            $this->relativeFromReal($this->findServicesRootPath(). DS . $service . "/database/migrations");
    }

    /**
     * Find the features root path in the given service.
     *
     * @param string $service
     *
     * @return string
     */
    public function findFeaturesRootPath($service)
    {
        return $this->findServicePath($service). DS . 'Features';
    }

    /**
     * Find the file path for the given feature.
     *
     * @param string $service
     * @param string $feature
     *
     * @return string
     */
    public function findFeaturePath($service, $feature)
    {
        return $this->findFeaturesRootPath($service). DS . "$feature.php";
    }

    /**
     * Find the test file path for the given feature.
     *
     * @param string $service
     * @param string $feature
     *
     * @return string
     */
    public function findFeatureTestPath($service, $test)
    {
        $root = $this->findFeatureTestsRootPath();

        if ($service) {
            $root .= DS . 'Services'. DS . $service;
        }

        return join(DS, [$root, "$test.php"]);
    }

    /**
     * Find the namespace for features in the given service.
     *
     * @param string $service
     *
     * @return string
     * @throws Exception
     */
    public function findFeatureNamespace($service, $feature)
    {
        $dirs = join('\\', explode(DS, dirname($feature)));

        $base = $this->findServiceNamespace($service).'\\Features';

        // greater than 1 because when there aren't subdirectories it will be "."
        if (strlen($dirs) > 1) {
            return $base.'\\'.$dirs;
        }

        return $base;
    }

    /**
     * Find the namespace for features tests in the given service.
     *
     * @param string $service
     *
     * @return string
     */
    public function findFeatureTestNamespace($service = null)
    {
        $namespace = $this->findFeatureTestsRootNamespace();

        if ($service) {
            $namespace .= "\\Services\\$service";
        }

        return $namespace;
    }

    /**
     * Find the operations root path in the given service.
     *
     * @param string $service
     *
     * @return string
     */
    public function findOperationsRootPath($service)
    {
        return $this->findServicePath($service). DS . 'Operations';
    }

    /**
     * Find the file path for the given operation.
     *
     * @param string $service
     * @param string $operation
     *
     * @return string
     */
    public function findOperationPath($service, $operation)
    {
        return $this->findOperationsRootPath($service). DS . "$operation.php";
    }

    /**
     * Find the test file path for the given operation.
     *
     * @param string $service
     * @param string $operation
     *
     * @return string
     */
    public function findOperationTestPath($service, $test)
    {
        $root = $this->findUnitTestsRootPath();

        if ($service) {
            $root .= DS . 'Services'. DS . $service;
        }

        return join(DS, [$root, 'Operations', "$test.php"]);
    }

    /**
     * Find the namespace for operations in the given service.
     *
     * @param string $service
     *
     * @return string
     * @throws Exception
     */
    public function findOperationNamespace($service)
    {
        return $this->findServiceNamespace($service).'\\Operations';
    }

    /**
     * Find the namespace for operations tests in the given service.
     *
     * @param string $service
     *
     * @return string
     * @throws Exception
     */
    public function findOperationTestNamespace($service = null)
    {
        $namespace = $this->findUnitTestsRootNamespace();

        if ($service) {
            $namespace .= "\\Services\\$service";
        }

        return $namespace . '\\Operations';
    }

    /**
     * Find the root path of domains.
     *
     * @return string
     */
    public function findDomainsRootPath()
    {
        return $this->findSourceRoot(). DS .'Domains';
    }

    /**
     * Find the path for the given domain.
     *
     * @param string $domain
     *
     * @return string
     */
    public function findDomainPath($domain)
    {
        return $this->findDomainsRootPath(). DS . $domain;
    }

    /**
     * Get the list of domains.
     *
     * @return Collection;
     */
    public function listDomains()
    {
        $finder = new SymfonyFinder();
        $directories = $finder
            ->depth(0)
            ->in($this->findDomainsRootPath())
            ->directories();

        $domains = new Collection();
        foreach ($directories as $directory) {
            $name = $directory->getRelativePathName();

            $domain = new Domain(
                Str::realName($name),
                $this->findDomainNamespace($name),
                $directory->getRealPath(),
                $this->relativeFromReal($directory->getRealPath())
            );

            $domains->push($domain);
        }

        return $domains;
    }

    /**
     * List the jobs per domain,
     * optionally provide a domain name to list its jobs.
     *
     * @param string $domainName
     *
     * @return Collection
     */
    public function listJobs($domainName = null)
    {
        $domains = ($domainName) ? [$this->findDomain(Str::domain($domainName))] : $this->listDomains();

        $jobs = new Collection();
        foreach ($domains as $domain) {
            $path = $domain->realPath;

            $finder = new SymfonyFinder();
            $files = $finder
                ->name('*Job.php')
                ->in($path. DS .'Jobs')
                ->files();

            $jobs[$domain->name] = new Collection();

            foreach ($files as $file) {
                $name = $file->getRelativePathName();
                $job = new Job(
                    Str::realName($name, '/Job.php/'),
                    $this->findDomainJobsNamespace($domain->name),
                    $name,
                    $file->getRealPath(),
                    $this->relativeFromReal($file->getRealPath()),
                    $domain,
                    file_get_contents($file->getRealPath())
                );

                $jobs[$domain->name]->push($job);
            }
        }

        return $jobs;
    }

    /**
     * Find the path for the given job name.
     *
     * @param  string$domain
     * @param  string$job
     *
     * @return string
     */
    public function findJobPath($domain, $job)
    {
        return $this->findDomainPath($domain).DS.'Jobs'.DS.$job.'.php';
    }

    /**
     * Find the namespace for the given domain.
     *
     * @param string $domain
     *
     * @return string
     * @throws Exception
     */
    public function findDomainNamespace($domain)
    {
        return $this->findRootNamespace().'\\Domains\\'.$domain;
    }

    /**
     * Find the namespace for the given domain's Jobs.
     *
     * @param string $domain
     *
     * @return string
     * @throws Exception
     */
    public function findDomainJobsNamespace($domain)
    {
        return $this->findDomainNamespace($domain).'\Jobs';
    }

    /**
     * Find the namespace for the given domain's Jobs.
     *
     * @param string $domain
     *
     * @return string
     * @throws Exception
     */
    public function findDomainJobsTestsNamespace($domain)
    {
        return $this->findUnitTestsRootNamespace() . "\\Domains\\$domain\\Jobs";
    }

    /**
     * Get the path to the tests of the given domain.
     *
     * @param string $domain
     *
     * @return string
     */
    public function findDomainTestsPath($domain)
    {
        return $this->findUnitTestsRootPath() . DS . 'Domains' . DS . $domain;
    }

    /**
     * Find the test path for the given job.
     *
     * @param string $domain
     * @param string $jobTest
     *
     * @return string
     */
    public function findJobTestPath($domain, $jobTest)
    {
        return $this->findDomainTestsPath($domain) . DS . 'Jobs' . DS . "$jobTest.php";
    }

    /**
     * Find the path for the give controller class.
     *
     * @param string $service
     * @param string $controller
     *
     * @return string
     */
    public function findControllerPath($service, $controller)
    {
        return $this->findServicePath($service).DS.join(DS, ['Http', 'Controllers', "$controller.php"]);
    }

    /**
     * Find the namespace of controllers in the given service.
     *
     * @param string $service
     *
     * @return string
     */
    public function findControllerNamespace($service)
    {
        return $this->findServiceNamespace($service).'\\Http\\Controllers';
    }

    /**
     * Get the list of services.
     *
     * @return Collection
     */
    public function listServices()
    {
        $services = new Collection();

        if (file_exists($this->findServicesRootPath())) {
            $finder = new SymfonyFinder();

            foreach ($finder->directories()->depth('== 0')->in($this->findServicesRootPath())->directories() as $dir) {
                $realPath = $dir->getRealPath();
                $services->push(new Service($dir->getRelativePathName(), $realPath, $this->relativeFromReal($realPath)));
            }
        }

        return $services;
    }

    /**
     * Find the service for the given service name.
     *
     * @param string $service
     *
     * @return Service
     * @throws Exception
     */
    public function findService($service)
    {
        $finder = new SymfonyFinder();
        $dirs = $finder->name($service)->in($this->findServicesRootPath())->directories();
        if ($dirs->count() < 1) {
            throw new Exception('Service "'.$service.'" could not be found.');
        }

        foreach ($dirs as $dir) {
            $path = $dir->getRealPath();

            return  new Service(Str::service($service), $path, $this->relativeFromReal($path));
        }
    }

    /**
     * Find the domain for the given domain name.
     *
     * @param string $domain
     *
     * @return Domain
     */
    public function findDomain($domain)
    {
        $finder = new SymfonyFinder();
        $dirs = $finder->name($domain)->in($this->findDomainsRootPath())->directories();
        if ($dirs->count() < 1) {
            throw new Exception('Domain "'.$domain.'" could not be found.');
        }

        foreach ($dirs as $dir) {
            $path = $dir->getRealPath();

            return  new Domain(
                Str::service($domain),
                $this->findDomainNamespace($domain),
                $path,
                $this->relativeFromReal($path)
            );
        }
    }

    /**
     * Find the feature for the given feature name.
     *
     * @param string $name
     *
     * @return Feature
     */
    public function findFeature($name)
    {
        $name = Str::feature($name);
        $fileName = "$name.php";

        $finder = new SymfonyFinder();
        $files = $finder->name($fileName)->in($this->findServicesRootPath())->files();
        foreach ($files as $file) {
            $path = $file->getRealPath();
            $serviceName = strstr($file->getRelativePath(), DS, true);
            $service = $this->findService($serviceName);
            $content = file_get_contents($path);

            return new Feature(
                Str::realName($name, '/Feature/'),
                $fileName,
                $path,
                $this->relativeFromReal($path),
                $service,
                $content
            );
        }
    }

    /**
     * Find the feature for the given feature name.
     *
     * @param string $name
     *
     * @return Job
     */
    public function findJob($name)
    {
        $name = Str::job($name);
        $fileName = "$name.php";

        $finder = new SymfonyFinder();
        $files = $finder->name($fileName)->in($this->findDomainsRootPath())->files();
        foreach ($files as $file) {
            $path = $file->getRealPath();
            $domainName = strstr($file->getRelativePath(), DIRECTORY_SEPARATOR, true);
            $domain = $this->findDomain($domainName);
            $content = file_get_contents($path);

            return new Job(
                Str::realName($name, '/Job/'),
                $this->findDomainJobsNamespace($domainName),
                $fileName,
                $path,
                $this->relativeFromReal($path),
                $domain,
                $content
            );
        }
    }

    /**
     * Get the list of features,
     * optionally withing a specified service.
     *
     * @param string $serviceName
     *
     * @return array of Feature
     *
     * @throws Exception
     */
    public function listFeatures($serviceName = '')
    {
        $services = $this->listServices();

        if (!empty($serviceName)) {
            $services = $services->filter(function ($service) use ($serviceName) {
                return $service->name === $serviceName || $service->slug === $serviceName;
            });

            if ($services->isEmpty()) {
                throw new InvalidArgumentException('Service "'.$serviceName.'" could not be found.');
            }
        }

        $features = [];
        foreach ($services as $service) {
            $serviceFeatures = new Collection();
            $finder = new SymfonyFinder();
            $files = $finder
                ->name('*Feature.php')
                ->in($this->findFeaturesRootPath($service->name))
                ->files();
            foreach ($files as $file) {
                $fileName = $file->getRelativePathName();
                $title = Str::realName($fileName, '/Feature.php/');
                $realPath = $file->getRealPath();
                $relativePath = $this->relativeFromReal($realPath);

                $serviceFeatures->push(new Feature($title, $fileName, $realPath, $relativePath, $service));
            }

            // add to the features array as [service_name => Collection(Feature)]
            $features[$service->name] = $serviceFeatures;
        }

        return $features;
    }

    /**
     * Get the path to the passed model.
     *
     * @param string $model
     *
     * @return string
     */
    public function findModelPath($model)
    {
        return $this->getSourceDirectoryName(). DS .'Data'. DS . 'Models' . DS . "$model.php";
    }

    /**
     * Get the path to the policies directory.
     *
     * @return string
     */
    public function findPoliciesPath()
    {
        return $this->getSourceDirectoryName().DS.'Policies';
    }

    /**
     * Get the path to the passed policy.
     *
     * @param string $policy
     *
     * @return string
     */
    public function findPolicyPath($policy)
    {
        return $this->findPoliciesPath().DS.$policy.'.php';
    }

    /**
     * Get the path to the request directory of a specific service.
     *
     * @param string $domain
     *
     * @return string
     */
    public function findRequestsPath($domain)
    {
        return $this->findDomainPath($domain). DS . 'Requests';
    }

    /**
     * Get the path to a specific request.
     *
     * @param string $domain
     * @param string $request
     *
     * @return string
     */
    public function findRequestPath($domain, $request)
    {
        return $this->findRequestsPath($domain) . DS . $request.'.php';
    }

    /**
     * Get the namespace for the Models.
     *
     * @return string
     * @throws Exception
     */
    public function findModelNamespace()
    {
        return $this->findRootNamespace().'\\Data\\Models';
    }

    /**
     * Get the namespace for Policies.
     *
     * @return mixed
     * @throws Exception
     */
    public function findPolicyNamespace()
    {
        return $this->findRootNamespace().'\\Policies';
    }

    /**
     * Get the requests namespace for the service passed in.
     *
     * @param string $domain
     *
     * @return string
     * @throws Exception
     */
    public function findRequestsNamespace($domain)
    {
        return $this->findDomainNamespace($domain).'\\Requests';
    }

    /**
     * Get the relative version of the given real path.
     *
     * @param string $path
     * @param string $needle
     *
     * @return string
     */
    protected function relativeFromReal($path, $needle = '')
    {
        if (!$needle) {
            $needle = $this->getSourceDirectoryName().DS;
        }

        return strstr($path, $needle);
    }

    /**
     * Get the path to the Composer.json file.
     *
     * @return string
     */
    protected function getComposerPath()
    {
        return app()->basePath().DS.'composer.json';
    }

    /**
     * Get the path to the given configuration file.
     *
     * @param string $name
     *
     * @return string
     */
    protected function getConfigPath($name)
    {
        return app()['path.config']. DS ."$name.php";
    }

    /**
     * Get the root path to unit tests directory.
     *
     * @return string
     */
    protected function findUnitTestsRootPath()
    {
        return base_path(). DS . 'tests' . DS . 'Unit';
    }

    /**
     * Get the root path to feature tests directory.
     *
     * @return string
     */
    protected function findFeatureTestsRootPath()
    {
        return base_path(). DS . 'tests' . DS . 'Feature';
    }

    /**
     * Get the root namespace for unit tests
     *
     * @return string
     */
    protected function findUnitTestsRootNamespace()
    {
        return 'Tests\\Unit';
    }

    protected function findFeatureTestsRootNamespace()
    {
        return 'Tests\\Feature';
    }
}
