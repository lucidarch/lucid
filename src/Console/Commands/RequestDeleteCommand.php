<?php

namespace Lucid\Console\Commands;

use Illuminate\Console\Command;
use Lucid\Filesystem;
use Lucid\Finder;
use Lucid\Str;

class RequestDeleteCommand extends Command
{
    use Filesystem, Finder;

    protected $signature = 'delete:request
                            {request : The Request\'s name.}
                            {service : The Service\'s name.}
                            ';

    protected $description = 'Delete an existing Request.';

    public function handle(): void
    {
        try {
            $request = Str::request($this->argument('request'));
            $service = Str::service($this->argument('service'));

            if (! $this->exists($path = $this->findRequestPath($service, $request))) {
                $this->error("Request class $request cannot be found.");
            } else {
                $this->delete($path);

                $this->info("Request class <comment>$request</comment> deleted successfully.");
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
