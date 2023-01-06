<?php

namespace Lucid\Console\Commands;

use Exception;
use Lucid\Generators\ModelGenerator;
use Lucid\Console\Command;
use Lucid\Filesystem;
use Lucid\Finder;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;

class ModelMakeCommand extends SymfonyCommand
{
    use Finder;
    use Command;
    use Filesystem;

    protected string $name = 'make:model';

    protected string $description = 'Create a new Eloquent Model.';

    /**
     * The type of class being generated
     */
    protected string $type = 'Model';

    public function handle(): void
    {
        $generator = new ModelGenerator();

        $name = $this->argument('model');

        try {
            $model = $generator->generate($name);

            $this->info('Model class created successfully.' .
                "\n" .
                "\n" .
                'Find it at <comment>' . $model->relativePath . '</comment>' . "\n"
            );
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    protected function getArguments(): array
    {
        return [
            ['model', InputArgument::REQUIRED, 'The Model\'s name.']
        ];
    }

    /**
     * Get the stub file for the generator.
     */
    public function getStub(): string
    {
        return __DIR__ . '/../Generators/stubs/model.stub';
    }
}
