<?php

namespace Lucid\Console\Commands;

use Lucid\Str;
use Lucid\Finder;
use Lucid\Console\Command;
use Lucid\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class OperationDeleteCommand extends SymfonyCommand
{
    use Finder;
    use Command;
    use Filesystem;

    protected string $name = 'delete:operation';

    protected string $description = 'Delete an existing Operation in a service';

    /**
     * The type of class being deleted.
     */
    protected string $type = 'Operation';

    public function handle(): void
    {
        try {
            $service = Str::service($this->argument('service'));
            $title = Str::operation($this->argument('operation'));

            if (!$this->exists($operation = $this->findOperationPath($service, $title))) {
                $this->error('Operation class '.$title.' cannot be found.');
            } else {
                $this->delete($operation);

                $this->info('Operation class <comment>'.$title.'</comment> deleted successfully.');
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    protected function getArguments(): array
    {
        return [
            ['operation', InputArgument::REQUIRED, 'The operation\'s name.'],
            ['service', InputArgument::OPTIONAL, 'The service from which the operation should be deleted.'],
        ];
    }

    protected function getStub(): string
    {
        return __DIR__ . '/../Generators/stubs/operation.stub';
    }
}
