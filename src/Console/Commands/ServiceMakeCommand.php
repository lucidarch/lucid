<?php

namespace Lucid\Console\Commands;

use Lucid\Finder;
use Lucid\Console\Command;
use Lucid\Filesystem;
use Lucid\Generators\ServiceGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class ServiceMakeCommand extends SymfonyCommand
{
    use Finder;
    use Command;
    use Filesystem;

    private string $namespace;
    private string $path;

    protected string $name = 'make:service';

    protected string $description = 'Create a new Service';

    public function handle(): void
    {
        try {
            $name = $this->argument('name');

            $generator = new ServiceGenerator();
            $service = $generator->generate($name);

            $this->info('Service '.$service->name.' created successfully.'."\n");

            $serviceNamespace = $this->findServiceNamespace($service->name);

            $serviceProvider = $serviceNamespace.'\\Providers\\'.$service->name.'ServiceProvider';

            $this->info('Activate it by adding '.
                '<comment>'.$serviceProvider.'::class</comment> '.
                "\nto <comment>'providers' in config/app.php</comment>".
                "\n"
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

    protected function getStub(): string
    {
        return __DIR__ . '/../Generators/stubs/service.stub';
    }
}
