<?php

namespace Lucid\Console\Commands;

use Illuminate\Console\Command;
use Lucid\Entities\Feature;
use Lucid\Finder;

class FeaturesListCommand extends Command
{
    use Finder;

    protected $signature = 'list:features
                       {service? : The service to list the features of.}
                       ';

    protected $description = 'List the features.';

    public function handle(): void
    {
        try {
            $featuresList = $this->listFeatures($this->argument('service'));
        } catch (\Exception $e) {
            $this->error($e->getMessage());

            return;
        }

        foreach ($featuresList as $service => $features) {
            $this->comment("\n$service\n");

            $this->table(
                ['Feature', 'Service', 'File', 'Path'],
                array_map(function (Feature $feature) {
                    return [$feature->title, $feature->service->name, $feature->file, $feature->relativePath];
                }, $features->all())
            );
        }
    }
}
