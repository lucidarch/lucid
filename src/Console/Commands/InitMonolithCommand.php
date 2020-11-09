<?php

namespace Lucid\Console\Commands;

use Lucid\Str;
use Lucid\Finder;
use Lucid\Filesystem;
use Lucid\Console\Command;
use Lucid\Generators\MonolithGenerator;
use Illuminate\Support\Composer;
use Symfony\Component\Console\Input\ArrayInput;
use Illuminate\Filesystem\Filesystem as IlluminateFilesystem;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class InitMonolithCommand extends SymfonyCommand
{
    use Finder;
    use Command;
    use Filesystem;
    use InitCommandTrait;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'init:monolith';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize Lucid Monolith in current project.';

    /**
     * The Composer class instance.
     *
     * @var Composer
     */
    protected $composer;

    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
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

        return 0;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['service', InputArgument::OPTIONAL, 'Your first service.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
        ];
    }
}
