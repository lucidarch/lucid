<?php

namespace Lucid\Console\Commands;

use Lucid\Str;
use Lucid\Finder;
use Lucid\Console\Command;
use Lucid\Filesystem;
use Lucid\Generators\FeatureGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class FeatureMakeCommand extends SymfonyCommand
{
    use Finder;
    use Command;
    use Filesystem;

    protected string $name = 'make:feature';

    protected string $description = 'Create a new Feature in a service';

    /**
     * The type of class being generated.
     */
    protected string $type = 'Feature';

    public function handle(): void
    {
        try {
            $service = Str::studly($this->argument('service'));
            $title = Str::feature($this->argument('feature'));

            $generator = new FeatureGenerator();
            $feature = $generator->generate($title, $service);

            $this->info(
                'Feature class '.$feature->title.' created successfully.'.
                "\n".
                "\n".
                'Find it at <comment>'.$feature->relativePath.'</comment>'."\n"
            );
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    protected function getArguments(): array
    {
        return [
            ['feature', InputArgument::REQUIRED, 'The feature\'s name.'],
            ['service', InputArgument::OPTIONAL, 'The service in which the feature should be implemented.'],
        ];
    }

    protected function getStub(): string
    {
        return __DIR__ . '/../Generators/stubs/feature.stub';
    }
}
