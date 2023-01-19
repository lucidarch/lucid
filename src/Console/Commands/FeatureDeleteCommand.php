<?php

namespace Lucid\Console\Commands;

use Lucid\Console\Command;
use Lucid\Filesystem;
use Lucid\Finder;
use Lucid\Str;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;

class FeatureDeleteCommand extends SymfonyCommand
{
    use Finder;
    use Command;
    use Filesystem;

    protected string $name = 'delete:feature';

    protected string $description = 'Delete an existing Feature in a service';

    public function handle(): void
    {
        $service = Str::service($this->argument('service'));
        $title = Str::feature($this->argument('feature'));

        try {
            // Delete feature
            if (! $this->exists($feature = $this->findFeaturePath($service, $title))) {
                $this->error("Feature class $title cannot be found.");
            } else {
                $this->delete($feature);

                $this->info("Feature class <comment>$title</comment> deleted successfully.");
            }

            // Delete feature tests
            $testTitle = $title.'Test';
            if (! $this->exists($test = $this->findFeatureTestPath($service, $testTitle))) {
                $this->error("Feature test class $testTitle cannot be found.");
            } else {
                $this->delete($test);

                $this->info("Feature test class <comment>$testTitle</comment> deleted successfully.");
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
}
