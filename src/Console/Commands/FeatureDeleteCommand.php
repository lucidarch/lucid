<?php

namespace Lucid\Console\Commands;

use Lucid\Str;
use Lucid\Finder;
use Lucid\Console\Command;
use Lucid\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class FeatureDeleteCommand extends SymfonyCommand
{
    use Finder;
    use Command;
    use Filesystem;

    protected string $name = 'delete:feature';

    protected string $description = 'Delete an existing Feature in a service';

    /**
     * The type of class being deleted.
     */
    protected string $type = 'Feature';

    public function handle(): void
    {
        try {
            $service = Str::service($this->argument('service'));
            $title = Str::feature($this->argument('feature'));

            if (!$this->exists($feature = $this->findFeaturePath($service, $title))) {
                $this->error('Feature class '.$title.' cannot be found.');
            } else {
                $this->delete($feature);

                $this->info('Feature class <comment>'.$title.'</comment> deleted successfully.');
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    protected function getArguments(): array
    {
        return [
            ['feature', InputArgument::REQUIRED, 'The feature\'s name.'],
            ['service', InputArgument::OPTIONAL, 'The service from which the feature should be deleted.'],
        ];
    }

    protected function getStub(): string
    {
        return __DIR__ . '/../Generators/stubs/feature.stub';
    }
}
