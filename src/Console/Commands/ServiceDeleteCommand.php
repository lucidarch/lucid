<?php

namespace Lucid\Console\Commands;

use Illuminate\Console\Command;
use Lucid\Filesystem;
use Lucid\Finder;
use Lucid\Str;

class ServiceDeleteCommand extends Command
{
    use Filesystem, Finder;

    protected $signature = 'delete:service
                            {name : The service name.}
                            ';

    protected $description = 'Delete an existing Service';

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
            $this->error((string) $e->getLine());
        }
    }
}
