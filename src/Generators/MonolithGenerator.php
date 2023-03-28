<?php

namespace Lucid\Generators;

class MonolithGenerator extends Generator
{
    use DirectoriesGeneratorTrait;

    private array $directories = [
        'app' => [
            'Data',
            'Domains',
            'Services',
            'Foundation',
            'Policies',
            'Data/Models',
        ],
    ];

    public function generate(): array
    {
        return $this->generateDirectories();
    }
}
