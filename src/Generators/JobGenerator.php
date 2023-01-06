<?php

namespace Lucid\Generators;

use Exception;
use Lucid\Str;
use Lucid\Entities\Job;

class JobGenerator extends Generator
{
    /**
     * @throws Exception
     */
    public function generate(string $job, string $domain, bool $isQueueable = false): Job
    {
        $job = Str::job($job);
        $domain = Str::domain($domain);
        $path = $this->findJobPath($domain, $job);

        if ($this->exists($path)) {
            throw new Exception('Job already exists');
        }

        // Make sure the domain directory exists
        $this->createDomainDirectory($domain);

        // Create the job
        $namespace = $this->findDomainJobsNamespace($domain);

        $content = file_get_contents($this->getStub($isQueueable));
        $content = Str::replace(
            ['{{job}}', '{{namespace}}', '{{unit_namespace}}'],
            [$job, $namespace, $this->findUnitNamespace()],
            $content
        );

        $this->createFile($path, $content);

        $this->generateTestFile($job, $domain);

        return new Job(
            $job,
            $namespace,
            basename($path),
            $path,
            $this->relativeFromReal($path),
            $this->findDomain($domain),
            $content
        );
    }

    /**
     * Generate test file.
     *
     * @throws Exception
     */
    private function generateTestFile(string $job, string $domain): void
    {
        $content = file_get_contents($this->getTestStub());

        $namespace = $this->findDomainJobsTestsNamespace($domain);
        $jobNamespace = $this->findDomainJobsNamespace($domain)."\\$job";
        $testClass = $job.'Test';

        $content = Str::replace(
            ['{{namespace}}', '{{testclass}}', '{{job}}', '{{job_namespace}}'],
            [$namespace, $testClass, Str::snake($job), $jobNamespace],
            $content
        );

        $path = $this->findJobTestPath($domain, $testClass);

        $this->createFile($path, $content);
    }

    /**
     * Create domain directory.
     */
    private function createDomainDirectory(string $domain)
    {
        $this->createDirectory($this->findDomainPath($domain).'/Jobs');
        $this->createDirectory($this->findDomainTestsPath($domain).'/Jobs');
    }

    /**
     * Get the stub file for the generator.
     */
    public function getStub(bool $isQueueable = false): string
    {
        if ($isQueueable) {
            return __DIR__ . '/stubs/job-queueable.stub';
        } else {
            return __DIR__ . '/stubs/job.stub';
        }
    }

    /**
     * Get the test stub file for the generator.
     */
    public function getTestStub(): string
    {
        return __DIR__ . '/stubs/job-test.stub';
    }
}
