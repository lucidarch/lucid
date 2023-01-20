<?php

namespace Lucid\Console\Commands;

use Illuminate\Console\Command;
use Lucid\Finder;
use Lucid\Generators\ServiceGenerator;

class ServiceMakeCommand extends Command
{
    use Finder;

    protected $signature = 'make:service
                            {name : The service name.}
                            ';

    protected $description = 'Create a new Service';

    public function handle(): void
    {
        try {
            $service = (new ServiceGenerator())
                ->generate($this->argument('name'));

            $this->info("Service $service->name created successfully.\n");

            $serviceNamespace = $this->findServiceNamespace($service->name);

            $serviceProvider = $serviceNamespace.'\\Providers\\'.$service->name.'ServiceProvider';

            $this->info(
                'Activate it by adding '
                ."<comment>$serviceProvider::class</comment>"
                ."\nto <comment>'providers' in config/app.php</comment>\n"
            );
        } catch (\Exception $e) {
            $this->error($e->getMessage()."\n".$e->getFile().' at '.$e->getLine());
        }
    }
}
