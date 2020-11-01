<?php

namespace Lucid\Console\Commands;

use Lucid\Finder;
use Lucid\Console\Command;
use Illuminate\Support\Composer;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Finder\Finder as SymfonyFinder;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class ChangeSourceNamespaceCommand extends SymfonyCommand
{
    use Finder;
    use Command;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'src:name';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the source directory namespace.';

    /**
     * The Composer class instance.
     *
     * @var \Illuminate\Support\Composer
     */
    protected $composer;

    /**
     * The filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Create a new key generator command.
     */
    public function __construct()
    {
        parent::__construct();

        $this->files = new Filesystem();
        $this->composer = new Composer($this->files);
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->setAppDirectoryNamespace();

            $this->setAppConfigNamespaces();

            $this->setComposerNamespace();

            $this->info('Lucid source directory namespace set!');

            $this->composer->dumpAutoloads();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * Set the namespace on the files in the app directory.
     */
    protected function setAppDirectoryNamespace()
    {
        $files = SymfonyFinder::create()
                            ->in(base_path())
                            ->exclude('vendor')
                            ->contains($this->findRootNamespace())
                            ->name('*.php');

        foreach ($files as $file) {
            $this->replaceNamespace($file->getRealPath());
        }
    }

    /**
     * Replace the App namespace at the given path.
     *
     * @param string $path
     */
    protected function replaceNamespace($path)
    {
        $search = [
            'namespace '.$this->findRootNamespace().';',
            $this->findRootNamespace().'\\',
        ];

        $replace = [
            'namespace '.$this->argument('name').';',
            $this->argument('name').'\\',
        ];

        $this->replaceIn($path, $search, $replace);
    }

    /**
     * Set the PSR-4 namespace in the Composer file.
     */
    protected function setComposerNamespace()
    {
        $this->replaceIn(
            $this->getComposerPath(), str_replace('\\', '\\\\', $this->findRootNamespace()).'\\\\', str_replace('\\', '\\\\', $this->argument('name')).'\\\\'
        );
    }

    /**
     * Set the namespace in the appropriate configuration files.
     */
    protected function setAppConfigNamespaces()
    {
        $search = [
            $this->findRootNamespace().'\\Providers',
            $this->findRootNamespace().'\\Foundation',
            $this->findRootNamespace().'\\Http\\Controllers\\',
        ];

        $replace = [
            $this->argument('name').'\\Providers',
            $this->argument('name').'\\Foundation',
            $this->argument('name').'\\Http\\Controllers\\',
        ];

        $this->replaceIn($this->getConfigPath('app'), $search, $replace);
    }

    /**
     * Replace the given string in the given file.
     *
     * @param string       $path
     * @param string|array $search
     * @param string|array $replace
     */
    protected function replaceIn($path, $search, $replace)
    {
        if ($this->files->exists($path)) {
            $this->files->put($path, str_replace($search, $replace, $this->files->get($path)));
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The source directory namespace.'],
        ];
    }
}
