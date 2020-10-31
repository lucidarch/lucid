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

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'list:features';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List the features.';

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        foreach ($this->listFeatures($this->argument('service')) as $service => $features) {
            $this->comment("\n$service\n");
            $features = array_map(function($feature) {
                return [$feature->title, $feature->service->name, $feature->file, $feature->relativePath];
            }, $features->all());
            $this->table(['Feature', 'Service', 'File', 'Path'], $features);
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['service', InputArgument::OPTIONAL, 'The service to list the features of.'],
        ];
    }
}
