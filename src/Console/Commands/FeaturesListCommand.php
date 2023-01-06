<?php

namespace Lucid\Console\Commands;

use Lucid\Finder;
use Lucid\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class FeaturesListCommand extends SymfonyCommand
{
    use Finder;
    use Command;

    protected string $name = 'list:features';

    protected string $description = 'List the features.';

    public function handle(): void
    {
        try {
            foreach ($this->listFeatures($this->argument('service')) as $service => $features) {
                $this->comment("\n$service\n");
                $features = array_map(function($feature) {
                    return [$feature->title, $feature->service->name, $feature->file, $feature->relativePath];
                }, $features->all());
                $this->table(['Feature', 'Service', 'File', 'Path'], $features);
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    protected function getArguments(): array
    {
        return [
            ['service', InputArgument::OPTIONAL, 'The service to list the features of.'],
        ];
    }
}
