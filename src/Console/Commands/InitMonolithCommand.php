<?php

namespace Lucid\Console\Commands;

use Lucid\Finder;
use Lucid\Filesystem;
use Lucid\Console\Command;
use Lucid\Generators\MonolithGenerator;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class InitMonolithCommand extends SymfonyCommand
{
    use Finder;
    use Command;
    use Filesystem;
    use InitCommandTrait;

    protected string $name = 'init:monolith';

    protected string $description = 'Initialize Lucid Monolith in current project.';

    public function handle(): void
    {
        $version = app()->version();
        $this->info("Initializing Lucid Monolith for Laravel $version\n");

        $service = $this->argument('service');

        $directories = (new MonolithGenerator())->generate();
        $this->comment('Created directories:');
        $this->comment(join("\n", $directories));

        // create service
        if ($service) {
            $this->getApplication()
                ->find('make:service')
                ->run(new ArrayInput(['name' => $service]), $this->output);

            $this->ask('Once done, press Enter/Return to continue...');
        }

        $this->welcome($service);
    }

    protected function getArguments(): array
    {
        return [
            ['service', InputArgument::OPTIONAL, 'Your first service.'],
        ];
    }
}
