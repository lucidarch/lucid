<?php

namespace Lucid\Console\Commands;

use Illuminate\Console\Command;
use Lucid\Generators\RequestGenerator;

class RequestMakeCommand extends Command
{
    protected $signature = 'make:request
                            {name : The name of the class.}
                            {domain : The Domain in which this request should be generated.}
                            ';

    protected $description = 'Create a Request in a domain.';

    public function handle(): void
    {
        try {
            $request = (new RequestGenerator())
                ->generate(
                    $this->argument('name'),
                    $this->argument('domain')
                );

            $this->info(
                'Request class created successfully.'
                ."\n\n"
                ."Find it at <comment>$request->relativePath</comment>\n"
            );
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
