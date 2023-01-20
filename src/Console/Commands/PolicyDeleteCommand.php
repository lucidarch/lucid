<?php

namespace Lucid\Console\Commands;

use Illuminate\Console\Command;
use Lucid\Filesystem;
use Lucid\Finder;
use Lucid\Str;

class PolicyDeleteCommand extends Command
{
    use Filesystem, Finder;

    protected $signature = 'delete:policy
                            {policy : The Policy\'s name.}
                            ';

    protected $description = 'Delete an existing Policy.';

    public function handle(): void
    {
        try {
            $policy = Str::policy($this->argument('policy'));

            if (! $this->exists($path = $this->findPolicyPath($policy))) {
                $this->error("Policy class $policy cannot be found.");
            } else {
                $this->delete($path);

                $this->info("Policy class <comment>$policy</comment> deleted successfully.");
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
