<?php

namespace Lucid\Console\Commands;

use Illuminate\Console\Command;
use Lucid\Generators\MonolithGenerator;

class InitMonolithCommand extends Command
{
    use InitCommandTrait;

    protected $signature = 'init:monolith
                       {service? : Your first service.}
                       ';

    protected $description = 'Initialize Lucid Monolith in current project.';

    public function handle(): void
    {
        $version = app()->version();
        $this->info("Initializing Lucid Monolith for Laravel $version\n");

        $service = $this->argument('service');

        $directories = (new MonolithGenerator())->generate();
        $this->comment('Created directories:');
        $this->comment(implode("\n", $directories));

        // create service
        if ($service) {
            $this->call('make:service', ['name' => $service]);
            $this->ask('Once done, press Enter/Return to continue...');
        }

        $this->welcome($service);
    }
}
