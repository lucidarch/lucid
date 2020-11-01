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

    public function __construct()
    {
        parent::__construct();

        $this->files = new IlluminateFilesystem();
        $this->composer = new Composer($this->files);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("Initializing Lucid Monolith...\n");

        $current = $this->findAppNamespace();
        if ($current == 'Framework' || $this->exists(base_path().'/src')) {
            $sure = $this->ask('This project may have already been initialized, it is not advised to initialize again. Are you sure you want to proceed? [y/n]');
            if (!$sure) {
                $this->info('');
                $this->info('Aborting initialization as requested.');
                return 0;
            }
        }

        $service = $this->argument('service');
        $namespace = $this->option('namespace') ?: $this->findAppNamespace();

        // rename namespace
        $this->replacePSRNamespace($namespace);

        $directories = (new MonolithGenerator())->generate($namespace);
        $this->comment('Created directories:');
        $this->comment(join("\n", $directories));

        // create service
        if ($service) {
            $this->getApplication()
                ->find('make:service')
                ->run(new ArrayInput(['name' => $service]), $this->output);
        }

        $this->info('');
        $this->info('Please do the following to complete initialization:');
        $this->comment("- Add $namespace\Foundation\ServiceProvider::class to 'providers' in config/app.php");

        if ($service) {
            $formatted = Str::service($service);
            $this->comment("- Register {$formatted}ServiceProvider::class in $namespace\Foundation\ServiceProvider::register");
        }

        $this->info('');
        $this->ask('Once done, press Enter/Return to continue...');

        $this->welcome();

        return 0;
    }

    private function replacePSRNamespace($namespace)
    {
        $current = $this->findAppNamespace();

        if ($current == 'Framework') {
            $this->error('Refrained from updating composer, it seems that it will cause a conflict.');
            return;
        }

        // change current namespace to Framework
        $this->getApplication()
            ->find('src:name')
            ->run(new ArrayInput(['name' => 'Framework']), $this->output);

        $composer = json_decode($this->files->get($this->getComposerPath()), true);
        $composer['autoload']['psr-4']["$namespace\\"] = 'src/';
        $this->files->put($this->getComposerPath(), json_encode($composer, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));

        $this->composer->dumpAutoloads();
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
            ['namespace', null, InputOption::VALUE_OPTIONAL, 'Set the namespace for the application (starting at src/).'],
        ];
    }
}
