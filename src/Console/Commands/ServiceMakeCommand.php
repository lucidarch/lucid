<?php

namespace Lucid\Console\Commands;

use Lucid\Console\Command;
use Lucid\Filesystem;
use Lucid\Finder;
use Lucid\Generators\ServiceGenerator;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;

class ServiceMakeCommand extends SymfonyCommand
{
    use Finder;
    use Command;
    use Filesystem;

    protected string $name = 'make:service';

    protected string $description = 'Create a new Service';

    public function handle(): void
    {
        $generator = new ServiceGenerator();

        $name = $this->argument('name');

        try {
            $service = $generator->generate($name);

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

    public function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The service name.'],
        ];
    }
}
