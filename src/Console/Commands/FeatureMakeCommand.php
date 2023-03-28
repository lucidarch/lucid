<?php

namespace Lucid\Console\Commands;

use Illuminate\Console\Command;
use Lucid\Filesystem;
use Lucid\Finder;
use Lucid\Generators\FeatureGenerator;
use Lucid\Str;

class FeatureMakeCommand extends Command
{
    use Filesystem, Finder;

    protected $signature = 'make:feature
                            {feature : The feature\'s name.}
                            {service? : The service in which the feature should be implemented.}
                            ';

    protected $description = 'Create a new Feature in a service';

    public function handle(): void
    {
        try {
            $feature = (new FeatureGenerator())
                ->generate(
                    Str::studly($this->argument('feature')),
                    Str::studly($this->argument('service'))
                );

            $this->info(
                "Feature class $feature->title created successfully."
                ."\n\n"
                ."Find it at <comment>$feature->relativePath</comment>\n"
            );
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
