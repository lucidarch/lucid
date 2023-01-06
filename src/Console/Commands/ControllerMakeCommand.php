<?php

namespace Lucid\Console\Commands;

use Lucid\Finder;
use Lucid\Console\Command;
use Lucid\Filesystem;
use Symfony\Component\Console\Input\InputOption;
use Lucid\Generators\ControllerGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class ControllerMakeCommand extends SymfonyCommand
{
    use Finder;
    use Command;
    use Filesystem;

    protected string $name = 'make:controller';

    protected string $description = 'Create a new resource Controller class in a service';

    public function handle(): void
    {
        $generator = new ControllerGenerator();

        $service = $this->argument('service');
        $name = $this->argument('controller');

        try {
            $controller = $generator->generate($name, $service, $this->option('resource'));

            $this->info(
                'Controller class created successfully.'
                . "\n\n"
                . "Find it at <comment>$controller</comment>\n"
            );
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    protected function getArguments(): array
    {
        return [
            ['controller', InputArgument::REQUIRED, 'The controller\'s name.'],
            ['service', InputArgument::OPTIONAL, 'The service in which the controller should be generated.'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            ['resource', null, InputOption::VALUE_NONE, 'Generate a resource controller class.'],
        ];
    }
}
