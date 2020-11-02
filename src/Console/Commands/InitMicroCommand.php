<?php

namespace Lucid\Console\Commands;

use Lucid\Finder;
use Lucid\Filesystem;
use Lucid\Console\Command;
use Lucid\Generators\MicroGenerator;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class InitMicroCommand extends SymfonyCommand
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
    protected $name = 'init:micro';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize Lucid Micro in current project.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $version = app()->version();
        $this->info("Initializing Lucid Micro for Laravel $version\n");

        $generator = new MicroGenerator();
        $paths = $generator->generate();

        $this->comment("Created directories:");
        $this->comment(join("\n", $paths));

        $this->welcome();

        return 0;
    }
}
