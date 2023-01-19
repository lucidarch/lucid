<?php

namespace Lucid\Console\Commands;

use Lucid\Console\Command;
use Lucid\Filesystem;
use Lucid\Finder;
use Lucid\Generators\OperationGenerator;
use Lucid\Str;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class OperationMakeCommand extends SymfonyCommand
{
    use Finder;
    use Command;
    use Filesystem;

    protected string $name = 'make:operation {--Q|queue}';

    protected string $description = 'Create a new Operation in a domain';

    public function handle(): void
    {
        $generator = new OperationGenerator();

        $service = Str::studly($this->argument('service'));
        $title = Str::operation($this->argument('operation'));
        $isQueueable = $this->option('queue');

        try {
            $operation = $generator->generate($title, $service, $isQueueable);

            $this->info(
                "Operation class $title created successfully."
                ."\n\n"
                ."Find it at <comment>$operation->relativePath</comment>\n"
            );
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function getArguments(): array
    {
        return [
            ['operation', InputArgument::REQUIRED, 'The operation\'s name.'],
            ['service', InputArgument::OPTIONAL, 'The service in which the operation should be implemented.'],
            ['jobs', InputArgument::IS_ARRAY, 'A list of Jobs Operation calls'],
        ];
    }

    public function getOptions(): array
    {
        return [
            ['queue', 'Q', InputOption::VALUE_NONE, 'Whether a operation is queueable or not.'],
        ];
    }
}
