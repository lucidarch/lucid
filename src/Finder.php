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

    /**
     * Get the source directory name.
     */
    public function getSourceDirectoryName(): string
    {
        return 'app';
    }

    /**
     * Determines whether this is a lucid microservice installation.
     */
    public function isMicroservice(): bool
    {
        return !file_exists(base_path().DS.$this->getSourceDirectoryName().DS.'Services');
    }

    /**
     * Get the namespace used for the application.
     *
     * @throws Exception
     */
    public function findNamespace(string $dir): string
    {
        // read composer.json file contents to determine the namespace
        $composer = json_decode(file_get_contents(base_path(). DS .'composer.json'), true);

        // see which one refers to the "src/" directory
        foreach ($composer['autoload']['psr-4'] as $namespace => $directory) {
            $directory = str_replace(['/', '\\'], DS, $directory ?? '');
            if ($directory === $dir.DS) {
                return trim($namespace, '\\');
            }
        }

        throw new Exception('App namespace not set in composer.json');
    }
    /**
     * @throws Exception
     */
    public function findRootNamespace(): string
    {
        return $this->findNamespace($this->getSourceDirectoryName());
    }

    /**
     * Find the namespace of a unit.
     */
    public function findUnitNamespace(): string
    {
        return 'Lucid\Units';
    }

    /**
     * Find the namespace for the given service name.
     *
     * @throws Exception
     */
    public function findServiceNamespace(?string $service = null): string
    {
        $root = $this->findRootNamespace();

        return (!$service) ? $root : "$root\\Services\\$service";
    }

    /**
     * Get the root of the source directory.
     */
    public function getSourceRoot(): string
    {
        return app_path();
    }

    /**
     * Find the root path of all the services.
     */
    public function findServicesRootPath(): string
    {
        return $this->getSourceRoot(). DS .'Services';
    }

    /**
     * Find the path to the directory of the given service name.
     * In the case of a microservice service installation this will be app path.
     */
    public function findServicePath(?string $service): string
    {
        return (!$service)
            ? $this->getSourceRoot()
            : $this->findServicesRootPath(). DS . $service;
    }

    /**
     * Find the path to the directory of the given service name.
     * In the case of a microservice service installation this will be app path.
     */
    public function findMigrationPath(?string $service): string
    {
        return (!$service)
            ? 'database/migrations'
            : $this->relativeFromReal($this->findServicesRootPath(). DS . $service . "/database/migrations");
    }

    /**
     * Find the features root path in the given service.
     */
    public function findFeaturesRootPath(?string $service): string
    {
        return $this->findServicePath($service). DS . 'Features';
    }

    /**
     * Find the file path for the given feature.
     */
    public function findFeaturePath(string $service, string $feature): string
    {
        return $this->findFeaturesRootPath($service). DS . "$feature.php";
    }

    /**
     * Find the test file path for the given feature.
     */
    public function findFeatureTestPath(?string $service, string $test): string
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
     * @throws Exception
     */
    public function findFeatureNamespace(?string $service, string $feature): string
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
     */
    public function findFeatureTestNamespace(?string $service = null): string
    {
        $namespace = $this->findFeatureTestsRootNamespace();

        if ($service) {
            $namespace .= "\\Services\\$service";
        }

        return $namespace;
    }

    /**
     * Find the operations root path in the given service.
     */
    public function findOperationsRootPath(?string $service): string
    {
        return $this->findServicePath($service). DS . 'Operations';
    }

    /**
     * Find the file path for the given operation.
     */
    public function findOperationPath(?string $service, string $operation): string
    {
        return $this->findOperationsRootPath($service) . DS . "$operation.php";
    }

    /**
     * Find the test file path for the given operation.
     */
    public function findOperationTestPath(?string $service, string $test): string
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
     * @throws Exception
     */
    public function findOperationNamespace(?string $service): string
    {
        return $this->findServiceNamespace($service).'\\Operations';
    }

    /**
     * Find the namespace for operations tests in the given service.
     *
     * @throws Exception
     */
    public function findOperationTestNamespace(?string $service = null): string
    {
        $namespace = $this->findUnitTestsRootNamespace();

        if ($service) {
            $namespace .= "\\Services\\$service";
        }

        return $namespace . '\\Operations';
    }

    /**
     * Find the root path of domains.
     */
    public function findDomainsRootPath(): string
    {
        return $this->getSourceRoot(). DS .'Domains';
    }

    /**
     * Find the path for the given domain.
     */
    public function findDomainPath(string $domain): string
    {
        return $this->findDomainsRootPath(). DS . $domain;
    }

    /**
     * Get the list of domains.
     *
     * @throws Exception
     */
    public function listDomains(): Collection
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
     * @throws Exception
     */
    public function listJobs(?string $domainName = null): Collection
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
     */
    public function findJobPath(string $domain, string $job): string
    {
        return $this->findDomainPath($domain).DS.'Jobs'.DS.$job.'.php';
    }

    /**
     * Find the namespace for the given domain.
     *
     * @throws Exception
     */
    public function findDomainNamespace(string $domain): string
    {
        return $this->findRootNamespace().'\\Domains\\'.$domain;
    }

    /**
     * Find the namespace for the given domain's Jobs.
     *
     * @throws Exception
     */
    public function findDomainJobsNamespace(string $domain): string
    {
        return $this->findDomainNamespace($domain).'\Jobs';
    }

    /**
     * Find the namespace for the given domain's Jobs.
     *
     * @throws Exception
     */
    public function findDomainJobsTestsNamespace(string $domain): string
    {
        return $this->findUnitTestsRootNamespace() . "\\Domains\\$domain\\Jobs";
    }

    /**
     * Get the path to the tests of the given domain.
     */
    public function findDomainTestsPath(string $domain): string
    {
        return $this->findUnitTestsRootPath() . DS . 'Domains' . DS . $domain;
    }

    /**
     * Find the test path for the given job.
     */
    public function findJobTestPath(string $domain, string $jobTest): string
    {
        return $this->findDomainTestsPath($domain) . DS . 'Jobs' . DS . "$jobTest.php";
    }

    /**
     * Find the path for the give controller class.
     */
    public function findControllerPath(?string $service, string $controller): string
    {
        return $this->findServicePath($service).DS.join(DS, ['Http', 'Controllers', "$controller.php"]);
    }

    /**
     * Find the namespace of controllers in the given service.
     *
     * @throws Exception
     */
    public function findControllerNamespace(?string $service): string
    {
        return $this->findServiceNamespace($service).'\\Http\\Controllers';
    }

    /**
     * Get the list of services.
     */
    public function listServices(): Collection
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
     * @throws Exception
     */
    public function findService(string $service): Service
    {
        $finder = new SymfonyFinder();
        $dirs = $finder->name($service)->in($this->findServicesRootPath())->directories();

        foreach ($dirs as $dir) {
            $path = $dir->getRealPath();

            return new Service(Str::service($service), $path, $this->relativeFromReal($path));
        }

        throw new Exception('Service "'.$service.'" could not be found.');
    }

    /**
     * Find the domain for the given domain name.
     *
     * @throws Exception
     */
    public function findDomain(string $domain): Domain
    {
        $finder = new SymfonyFinder();
        $dirs = $finder->name($domain)->in($this->findDomainsRootPath())->directories();

        foreach ($dirs as $dir) {
            $path = $dir->getRealPath();

            return  new Domain(
                Str::service($domain),
                $this->findDomainNamespace($domain),
                $path,
                $this->relativeFromReal($path)
            );
        }

        throw new Exception('Domain "'.$domain.'" could not be found.');
    }

    /**
     * Find the feature for the given feature name.
     *
     * @throws Exception
     */
    public function findFeature(string $name): Feature
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

        throw new Exception('Feature "'.$name.'" could not be found.');
    }

    /**
     * Find the feature for the given feature name.
     *
     * @throws Exception
     */
    public function findJob(string $name): Job
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

        throw new Exception('Job "'.$name.'" could not be found.');
    }

    /**
     * Get the list of features,
     * optionally withing a specified service.
     *
     * @return array<Collection>
     *
     * @throws Exception
     */
    public function listFeatures(?string $serviceName = ''): array
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
     */
    public function findModelPath(string $model): string
    {
        return $this->getSourceDirectoryName(). DS .'Data'. DS . 'Models' . DS . "$model.php";
    }

    /**
     * Get the path to the policies directory.
     */
    public function findPoliciesPath(): string
    {
        return $this->getSourceDirectoryName().DS.'Policies';
    }

    /**
     * Get the path to the passed policy.
     */
    public function findPolicyPath(string $policy): string
    {
        return $this->findPoliciesPath().DS.$policy.'.php';
    }

    /**
     * Get the path to the request directory of a specific service.
     */
    public function findRequestsPath(string $domain): string
    {
        return $this->findDomainPath($domain). DS . 'Requests';
    }

    /**
     * Get the path to a specific request.
     */
    public function findRequestPath(string $domain, string $request): string
    {
        return $this->findRequestsPath($domain) . DS . $request.'.php';
    }

    /**
     * Get the namespace for the Models.
     *
     * @throws Exception
     */
    public function findModelNamespace(): string
    {
        return $this->findRootNamespace().'\\Data\\Models';
    }

    /**
     * Get the namespace for Policies.
     *
     * @throws Exception
     */
    public function findPolicyNamespace(): string
    {
        return $this->findRootNamespace().'\\Policies';
    }

    /**
     * Get the requests namespace for the service passed in.
     *
     * @throws Exception
     */
    public function findRequestsNamespace(string $domain): string
    {
        return $this->findDomainNamespace($domain).'\\Requests';
    }

    /**
     * Get the relative version of the given real path.
     */
    protected function relativeFromReal(string $path, string $needle = ''): string
    {
        if ($needle === '') {
            $needle = $this->getSourceDirectoryName().DS;
        }

        return strstr($path, $needle);
    }

    /**
     * Get the path to the Composer.json file.
     */
    protected function getComposerPath(): string
    {
        return app()->basePath().DS.'composer.json';
    }

    /**
     * Get the path to the given configuration file.
     */
    protected function getConfigPath(string $name): string
    {
        return app()['path.config']. DS ."$name.php";
    }

    /**
     * Get the root path to unit tests directory.
     */
    protected function findUnitTestsRootPath(): string
    {
        return base_path(). DS . 'tests' . DS . 'Unit';
    }

    /**
     * Get the root path to feature tests directory.
     */
    protected function findFeatureTestsRootPath(): string
    {
        return base_path(). DS . 'tests' . DS . 'Feature';
    }

    /**
     * Get the root namespace for unit tests
     */
    protected function findUnitTestsRootNamespace(): string
    {
        return 'Tests\\Unit';
    }

    /**
     * Get the root namespace for feature tests
     */
    protected function findFeatureTestsRootNamespace(): string
    {
        return 'Tests\\Feature';
    }
}
