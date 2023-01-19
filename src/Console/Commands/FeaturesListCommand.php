<?php

namespace Lucid\Console\Commands;

use Lucid\Console\Command;
use Lucid\Entities\Feature;
use Lucid\Finder;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;

class FeaturesListCommand extends SymfonyCommand
{
    use Finder;
    use Command;

    protected string $name = 'list:features';

    protected string $description = 'List the features.';

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

    protected function getArguments(): array
    {
        return [
            ['service', InputArgument::OPTIONAL, 'The service to list the features of.'],
        ];
    }
}
