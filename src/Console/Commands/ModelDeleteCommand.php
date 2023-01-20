<?php

namespace Lucid\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Lucid\Filesystem;
use Lucid\Finder;
use Lucid\Str;

class ModelDeleteCommand extends Command
{
    use Filesystem, Finder;

    protected $signature = 'delete:model
                            {model : The Model\'s name.}
                            ';

    protected $description = 'Delete an existing Eloquent Model.';

    public function handle(): void
    {
        try {
            $model = Str::model($this->argument('model'));

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
}
