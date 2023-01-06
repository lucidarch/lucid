<?php

namespace Lucid\Console\Commands;

use Exception;
use Lucid\Str;
use Lucid\Console\Command;
use Lucid\Filesystem;
use Lucid\Finder;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;

class RequestDeleteCommand extends SymfonyCommand
{
    use Finder;
    use Command;
    use Filesystem;

    protected string $name = 'delete:request';

    protected string $description = 'Delete an existing Request.';

    /**
     * The type of class being generated
     */
    protected string $type = 'Request';

    public function handle(): void
    {
        try {
            $request = Str::request($this->argument('request'));
            $service = Str::service($this->argument('service'));

            if ( ! $this->exists($path = $this->findRequestPath($service, $request))) {
                $this->error('Request class ' . $request . ' cannot be found.');
            } else {
                $this->delete($path);

                $this->info('Request class <comment>' . $request . '</comment> deleted successfully.');
            }
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function getArguments(): array
    {
        return [
            ['request', InputArgument::REQUIRED, 'The Request\'s name.'],
            ['service', InputArgument::REQUIRED, 'The Service\'s name.'],
        ];
    }

    public function getStub(): string
    {
        return __DIR__ . '/../Generators/stubs/request.stub';
    }
}
