<?php

namespace Lucid\Console\Commands;

use Exception;
use Lucid\Console\Command;
use Lucid\Filesystem;
use Lucid\Finder;
use Lucid\Generators\RequestGenerator;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;

class RequestMakeCommand extends SymfonyCommand
{
    use Finder;
    use Command;
    use Filesystem;

    protected string $name = 'make:request';

    protected string $description = 'Create a Request in a domain.';

    public function handle(): void
    {
        $generator = new RequestGenerator();

        $name = $this->argument('name');
        $service = $this->argument('domain');

        try {
            $request = $generator->generate($name, $service);

            $this->info(
                'Request class created successfully.'
                ."\n\n"
                ."Find it at <comment>$request->relativePath</comment>\n"
            );
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the class.'],
            ['domain', InputArgument::REQUIRED, 'The Domain in which this request should be generated.'],
        ];
    }
}
