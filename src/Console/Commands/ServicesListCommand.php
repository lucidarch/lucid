<?php

namespace Lucid\Console\Commands;

use Illuminate\Console\Command;
use Lucid\Entities\Service;
use Lucid\Finder;

class ServicesListCommand extends Command
{
    use Finder;

    protected $signature = 'list:services';

    protected $description = 'List the services in this project.';

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
