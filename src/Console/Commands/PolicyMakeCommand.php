<?php

namespace Lucid\Console\Commands;

use Illuminate\Console\Command;
use Lucid\Generators\PolicyGenerator;

class PolicyMakeCommand extends Command
{
    protected $signature = 'make:policy
                            {policy : The Policy\'s name.}
                            ';

    protected $description = 'Create a Policy.';

    public function handle(): void
    {
        try {
            $policy = (new PolicyGenerator())
                ->generate($this->argument('policy'));

            $this->info(
                'Policy class created successfully.'
                ."\n\n"
                ."Find it at <comment>$policy->relativePath</comment>\n"
            );
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
