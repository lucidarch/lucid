<?php

namespace Lucid\Console\Commands;

use Illuminate\Console\Command;
use Lucid\Filesystem;
use Lucid\Finder;
use Lucid\Str;

class JobDeleteCommand extends Command
{
    use Filesystem, Finder;

    protected $signature = 'delete:job
                            {job : The job\'s name.}
                            {domain : The domain from which the job will be deleted.}
                            ';

    protected $description = 'Delete an existing Job in a domain';

    public function handle(): void
    {
        try {
            $domain = Str::studly($this->argument('domain'));
            $title = Str::job($this->argument('job'));

            // Delete job
            if (! $this->exists($job = $this->findJobPath($domain, $title))) {
                $this->error("Job class $title cannot be found.");
            } else {
                $this->delete($job);

                if (count($this->listJobs($domain)->first()) === 0) {
                    $this->delete($this->findDomainPath($domain));
                }

                $this->info("Job class <comment>$title</comment> deleted successfully.");
            }

            // Delete job tests
            $testTitle = $title.'Test';
            if (! $this->exists($job = $this->findJobTestPath($domain, $testTitle))) {
                $this->error("Job test class $testTitle cannot be found.");
            } else {
                $this->delete($job);

                $this->info("Job test class <comment>$testTitle</comment> deleted successfully.");
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
