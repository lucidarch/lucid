<?php

namespace Lucid\Console\Commands;

use Illuminate\Console\Command;
use Lucid\Generators\JobGenerator;
use Lucid\Str;

class JobMakeCommand extends Command
{
    protected $signature = 'make:job
                            {job : The job\'s name.}
                            {domain : The domain to be responsible for the job.}
                            {--Q|queue : Whether a job is queueable or not.}
                            ';

    protected $description = 'Create a new Job in a domain';

    public function handle(): void
    {
        try {
            $job = (new JobGenerator())
                ->generate(
                    Str::job($this->argument('job')),
                    Str::studly($this->argument('domain')),
                    $this->option('queue')
                );

            $this->info(
                "Job class $job->title created successfully."
                ."\n\n"
                ."Find it at <comment>$job->relativePath</comment>\n"
            );
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
