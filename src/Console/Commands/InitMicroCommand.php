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

    protected string $name = 'init:micro';

    protected string $description = 'Initialize Lucid Micro in current project.';

    public function handle(): void
    {
        $version = app()->version();
        $this->info("Initializing Lucid Micro for Laravel $version\n");

        $generator = new MicroGenerator();
        $paths = $generator->generate();

        $this->comment('Created directories:');
        $this->comment(join("\n", $paths));

        $this->welcome();
    }
}
