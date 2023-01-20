<?php

namespace Lucid\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;
use Lucid\Finder;
use Symfony\Component\Finder\Finder as SymfonyFinder;

class ChangeSourceNamespaceCommand extends Command
{
    use Finder;

    protected $signature = 'src:name
                            {name : The source directory namespace.}
                            ';

    protected $description = 'Set the source directory namespace.';

    /**
     * The Composer class instance.
     */
    protected Composer $composer;

    /**
     * The filesystem instance.
     */
    protected Filesystem $files;

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
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * Set the namespace on the files in the app directory.
     *
     * @throws Exception
     */
    protected function setAppDirectoryNamespace(): void
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
     * @throws Exception
     */
    protected function replaceNamespace(string $path): void
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
     *
     * @throws Exception
     */
    protected function setComposerNamespace(): void
    {
        $this->replaceIn(
            $this->getComposerPath(), str_replace('\\', '\\\\', $this->findRootNamespace()).'\\\\', str_replace('\\', '\\\\', $this->argument('name')).'\\\\'
        );
    }

    /**
     * Set the namespace in the appropriate configuration files.
     *
     * @throws Exception
     */
    protected function setAppConfigNamespaces(): void
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
     */
    protected function replaceIn(
        string $path,
        string|array $search,
        string|array $replace
    ): void {
        if ($this->files->exists($path)) {
            $this->files->put($path, str_replace($search, $replace, $this->files->get($path)));
        }
    }
}
