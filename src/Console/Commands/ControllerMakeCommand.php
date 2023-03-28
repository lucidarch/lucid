<?php

namespace Lucid\Console\Commands;

use Illuminate\Console\Command;
use Lucid\Generators\ControllerGenerator;

class ControllerMakeCommand extends Command
{
    protected $signature = 'make:controller
                            {controller : The controller\'s name.}
                            {service? : The service in which the controller should be generated.}
                            {--resource : Generate a resource controller class.}
                            ';

    protected $description = 'Create a new resource Controller class in a service';

    public function handle(): void
    {
        try {
            $controller = (new ControllerGenerator())
                ->generate(
                    $this->argument('controller'),
                    $this->argument('service'),
                    $this->option('resource')
                );

            $this->info(
                'Controller class created successfully.'
                ."\n\n"
                ."Find it at <comment>$controller</comment>\n"
            );
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
