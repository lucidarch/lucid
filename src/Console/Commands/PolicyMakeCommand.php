<?php

namespace Lucid\Console\Commands;

use Exception;
use Lucid\Generators\PolicyGenerator;
use Lucid\Console\Command;
use Lucid\Filesystem;
use Lucid\Finder;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;

class PolicyMakeCommand extends SymfonyCommand
{
    use Finder;
    use Command;
    use Filesystem;

    protected string $name = 'make:policy';

    protected string $description = 'Create a Policy.';

    public function handle(): void
    {
        $generator = new PolicyGenerator();

        $name = $this->argument('policy');

        try {
            $policy = $generator->generate($name);

            $this->info(
                'Policy class created successfully.'
                . "\n\n"
                . "Find it at <comment>$policy->relativePath</comment>\n"
            );
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    protected function getArguments(): array
    {
        return [
            ['policy', InputArgument::REQUIRED, 'The Policy\'s name.']
        ];
    }
}
