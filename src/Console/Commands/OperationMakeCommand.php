<?php

namespace Lucid\Console\Commands;

use Illuminate\Console\Command;
use Lucid\Generators\OperationGenerator;
use Lucid\Str;

class OperationMakeCommand extends Command
{
    protected $signature = 'make:operation
                            {operation : The operation\'s name.}
                            {service? : The service in which the operation should be implemented.}
                            {--Q|queue : Whether an operation is queueable or not.}
                            ';

    protected $description = 'Create a new Operation in a domain';

    public function handle(): void
    {
        try {
            $operation = (new OperationGenerator())
                ->generate(
                    Str::operation($this->argument('operation')),
                    Str::studly($this->argument('service')),
                    $this->option('queue')
                );

            $this->info(
                "Operation class $operation->title created successfully."
                ."\n\n"
                ."Find it at <comment>$operation->relativePath</comment>\n"
            );
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
