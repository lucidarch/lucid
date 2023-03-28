<?php

namespace Lucid\Console\Commands;

use Illuminate\Console\Command;
use Lucid\Generators\ModelGenerator;

class ModelMakeCommand extends Command
{
    protected $signature = 'make:model
                            {model : The Model\'s name.}
                            ';

    protected $description = 'Create a new Eloquent Model.';

    public function handle(): void
    {
        try {
            $model = (new ModelGenerator())
                ->generate($this->argument('model'));

            $this->info(
                'Model class created successfully.'
                ."\n\n"
                ."Find it at <comment>$model->relativePath</comment>\n"
            );
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
