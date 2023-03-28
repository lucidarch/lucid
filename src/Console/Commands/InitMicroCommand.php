<?php

namespace Lucid\Console\Commands;

use Illuminate\Console\Command;
use Lucid\Generators\MicroGenerator;

class InitMicroCommand extends Command
{
    use InitCommandTrait;

    protected $signature = 'init:micro';

    protected $description = 'Initialize Lucid Micro in current project.';

    public function handle(): void
    {
        $version = app()->version();
        $this->info("Initializing Lucid Micro for Laravel $version\n");

        $paths = (new MicroGenerator())->generate();

        $this->comment('Created directories:');
        $this->comment(implode("\n", $paths));

        $this->welcome();
    }
}
