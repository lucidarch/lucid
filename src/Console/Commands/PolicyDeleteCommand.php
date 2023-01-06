<?php

namespace Lucid\Console\Commands;

use Exception;
use Lucid\Str;
use Lucid\Console\Command;
use Lucid\Filesystem;
use Lucid\Finder;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;

class PolicyDeleteCommand extends SymfonyCommand
{
    use Finder;
    use Command;
    use Filesystem;

    protected string $name = 'delete:policy';

    protected string $description = 'Delete an existing Policy.';

    public function handle(): void
    {
        $policy = Str::policy($this->argument('policy'));

        try {
            if (! $this->exists($path = $this->findPolicyPath($policy))) {
                $this->error("Policy class $policy cannot be found.");
            } else {
                $this->delete($path);

                $this->info("Policy class <comment>$policy</comment> deleted successfully.");
            }
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function getArguments(): array
    {
        return [
            ['policy', InputArgument::REQUIRED, 'The Policy\'s name.']
        ];
    }
}
