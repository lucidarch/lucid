<?php

namespace Lucid\Console\Commands;

use Lucid\Console\Command;
use Lucid\Filesystem;
use Lucid\Finder;
use Lucid\Generators\JobGenerator;
use Lucid\Str;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class JobMakeCommand extends SymfonyCommand
{
    use Finder;
    use Command;
    use Filesystem;

    protected string $name = 'make:job {--Q|queue}';

    protected string $description = 'Create a new Job in a domain';

    /**
     * The type of class being generated.
     */
    protected string $type = 'Job';

    public function handle(): void
    {
        $generator = new JobGenerator();

        $domain = Str::studly($this->argument('domain'));
        $title = Str::job($this->argument('job'));
        $isQueueable = $this->option('queue');
        try {
            $job = $generator->generate($title, $domain, $isQueueable);

            $this->info(
                'Job class '.$title.' created successfully.'.
                "\n".
                "\n".
                'Find it at <comment>'.$job->relativePath.'</comment>'."\n"
            );
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    protected function getArguments(): array
    {
        return [
            ['job', InputArgument::REQUIRED, 'The job\'s name.'],
            ['domain', InputArgument::REQUIRED, 'The domain to be responsible for the job.'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            ['queue', 'Q', InputOption::VALUE_NONE, 'Whether a job is queueable or not.'],
        ];
    }

    public function getStub(): string
    {
        return __DIR__ . '/../Generators/stubs/job.stub';
    }
}
