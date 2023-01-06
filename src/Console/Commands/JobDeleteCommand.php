<?php

namespace Lucid\Console\Commands;

use Lucid\Str;
use Lucid\Finder;
use Lucid\Console\Command;
use Lucid\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class JobDeleteCommand extends SymfonyCommand
{
    use Finder;
    use Command;
    use Filesystem;

    protected string $name = 'delete:job';

    protected string $description = 'Delete an existing Job in a domain';

    public function handle(): void
    {
        try {
            $domain = Str::studly($this->argument('domain'));
            $title = Str::job($this->argument('job'));

            // Delete job
            if (!$this->exists($job = $this->findJobPath($domain, $title))) {
                $this->error("Job class $title cannot be found.");
            } else {
                $this->delete($job);

                if (count($this->listJobs($domain)->first()) === 0) {
                    $this->delete($this->findDomainPath($domain));
                }

                $this->info("Job class <comment>$title</comment> deleted successfully.");
            }

            // Delete job tests
            $testTitle = $title . 'Test';
            if (!$this->exists($job = $this->findJobTestPath($domain, $testTitle))) {
                $this->error("Job test class $testTitle cannot be found.");
            } else {
                $this->delete($job);

                $this->info("Job test class <comment>$testTitle</comment> deleted successfully.");
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function getArguments(): array
    {
        return [
            ['job', InputArgument::REQUIRED, 'The job\'s name.'],
            ['domain', InputArgument::REQUIRED, 'The domain from which the job will be deleted.'],
        ];
    }
}
