<?php

namespace Lucid\Generators;

class MonolithGenerator extends Generator
{
    use DirectoriesGeneratorTrait;

    private $directories = [
        'app' => [
            'Data',
            'Domains',
            'Services',
            'Foundation',
            'Policies',
            'Data/Models',
        ]
    ];

    public function generate()
    {
        return $this->generateDirectories();
    }
}
