<?php

namespace Lucid\Generators;

use Exception;
use Lucid\Str;
use Lucid\Entities\Operation;

class OperationGenerator extends Generator
{
    /**
     * @throws Exception
     */
    public function generate(
        string $operation,
        ?string $service,
        bool $isQueueable = false,
        array $jobs = []
    ): Operation
    {
        $operation = Str::operation($operation);
        $service = Str::service($service);

        $path = $this->findOperationPath($service, $operation);

        if ($this->exists($path)) {
            throw new Exception('Operation already exists!');
        }

        $namespace = $this->findOperationNamespace($service);

        $content = file_get_contents($this->getStub($isQueueable));

        list($useJobs, $runJobs) = self::getUsesAndRunners($jobs);

        $content = Str::replace(
            ['{{operation}}', '{{namespace}}', '{{unit_namespace}}', '{{use_jobs}}', '{{run_jobs}}'],
            [$operation, $namespace, $this->findUnitNamespace(), $useJobs, $runJobs],
            $content
        );

        $this->createFile($path, $content);

        // generate test file
        $this->generateTestFile($operation, $service);

        return new Operation(
            $operation,
            basename($path),
            $path,
            $this->relativeFromReal($path),
            ($service) ? $this->findService($service) : null,
            $content
        );
    }

    /**
     * Generate the test file.
     *
     * @throws Exception
     */
    private function generateTestFile(string $operation, ?string $service): void
    {
        $content = file_get_contents($this->getTestStub());

        $namespace = $this->findOperationTestNamespace($service);
        $operationNamespace = $this->findOperationNamespace($service)."\\$operation";
        $testClass = $operation.'Test';

        $content = Str::replace(
            ['{{namespace}}', '{{testclass}}', '{{operation}}', '{{operation_namespace}}'],
            [$namespace, $testClass, Str::snake($operation), $operationNamespace],
            $content
        );

        $path = $this->findOperationTestPath($service, $testClass);

        $this->createFile($path, $content);
    }

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(bool $isQueueable = false): string
    {
        if ($isQueueable) {
            return __DIR__ . '/stubs/operation-queueable.stub';
        } else {
            return __DIR__ . '/stubs/operation.stub';
        }
    }

    /**
     * Get the test stub file for the generator.
     */
    private function getTestStub(): string
    {
        return __DIR__ . '/stubs/operation-test.stub';
    }

    /**
     * Get de use to import the right class
     * Get de job run command
     */
    static private function getUseAndJobRunCommand(string $job): array
    {
        $str = Str::replaceLast('\\', '#', $job);
        $explode = explode('#', $str);

        $use = 'use '.$explode[0].'\\'.$explode['1'].";\n";
        $runJobs = "\t\t".'$this->run('.$explode['1'].'::class);';

        return [$use, $runJobs];
    }

    /**
     * Returns all users and all $this->run() generated
     */
    static private function getUsesAndRunners(array $jobs): array
    {
        $useJobs = '';
        $runJobs = '';
        foreach ($jobs as $index => $job) {

            list($useLine, $runLine) = self::getUseAndJobRunCommand($job);
            $useJobs .= $useLine;
            $runJobs .= $runLine;
            // only add carriage returns when it's not the last job
            if ($index != count($jobs) - 1) {
                $runJobs .= "\n\n";
            }
        }
        return [$useJobs, $runJobs];
    }


}
