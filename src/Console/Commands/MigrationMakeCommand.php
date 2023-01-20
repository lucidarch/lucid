<?php

namespace Lucid\Console\Commands;

use Illuminate\Console\Command;
use Lucid\Finder;
use Lucid\Str;

class MigrationMakeCommand extends Command
{
    use Finder;

    protected $signature = 'make:migration
                            {migration : The migration\'s name.}
                            {service? : The service in which the migration should be generated.}
                            ';

    protected $description = 'Create a new Migration class in a service';

    public function handle(): void
    {
        $path = $this
            ->findMigrationPath(Str::service($this->argument('service')));

        $this->call('make:migration', [
            'name' => $this->argument('migration'),
            '--path' => $path,
        ]);

        $this->info("\n Find it at <comment>$path</comment> \n");
    }
}
