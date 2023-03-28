<?php

namespace Lucid\Console\Commands;

use Illuminate\Console\Command;
use Lucid\Filesystem;
use Lucid\Finder;
use Lucid\Str;

class FeatureDeleteCommand extends Command
{
    use Filesystem, Finder;

    protected $signature = 'delete:feature
                            {feature : The feature\'s name.}
                            {service? : The service in which the feature should be deleted.}
                            ';

    protected $description = 'Delete an existing Feature in a service';

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
}
