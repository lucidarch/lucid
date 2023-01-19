<?php

namespace Lucid\Console\Commands;

use Lucid\Console\Command;
use Lucid\Filesystem;
use Lucid\Finder;
use Lucid\Str;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;

class ServiceDeleteCommand extends SymfonyCommand
{
    use Finder;
    use Command;
    use Filesystem;

    protected string $name = 'delete:service';

    protected string $description = 'Delete an existing Service';

    public function handle(): void
    {
        if ($this->isMicroservice()) {
            $this->error('This functionality is disabled in a Microservice');

            return;
        }

        $name = Str::service($this->argument('name'));

        try {
            if (! $this->exists($service = $this->findServicePath($name))) {
                $this->error("Service $name cannot be found.");

                return;
            }

            $this->delete($service);

            $this->info("Service <comment>$name</comment> deleted successfully \n");

            $this->info('Please remove your registered service providers, if any.');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            $this->error($e->getFile());
            $this->error($e->getLine());
        }
    }

    public function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The service name.'],
        ];
    }
}
