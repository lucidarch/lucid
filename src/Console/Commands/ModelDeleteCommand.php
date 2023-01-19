<?php

namespace Lucid\Console\Commands;

use Exception;
use Lucid\Console\Command;
use Lucid\Filesystem;
use Lucid\Finder;
use Lucid\Str;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;

class ModelDeleteCommand extends SymfonyCommand
{
    use Finder;
    use Command;
    use Filesystem;

    protected string $name = 'delete:model';

    protected string $description = 'Delete an existing Eloquent Model.';

    public function handle(): void
    {
        $model = Str::model($this->argument('model'));

        try {
            if (! $this->exists($path = $this->findModelPath($model))) {
                $this->error("Model class $model cannot be found.");
            } else {
                $this->delete($path);

                $this->info("Model class <comment>$model</comment> deleted successfully.");
            }
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function getArguments(): array
    {
        return [
            ['model', InputArgument::REQUIRED, 'The Model\'s name.'],
        ];
    }
}
