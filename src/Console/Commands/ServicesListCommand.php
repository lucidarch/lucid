<?php

namespace Lucid\Console\Commands;

use Lucid\Entities\Service;
use Lucid\Finder;
use Lucid\Console\Command;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class ServicesListCommand extends SymfonyCommand
{
    use Finder;
    use Command;

    protected string $name = 'list:services';

    protected string $description = 'List the services in this project.';

    public function handle(): void
    {
        $services = $this->listServices()->all();

        $this->table(
            ['Service', 'Slug', 'Path'],
            array_map(function (Service $service) {
                return [$service->name, $service->slug, $service->relativePath];
            }, $services)
        );
    }
}
