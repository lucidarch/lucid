<?php

namespace Lucid\Generators;

class MonolithGenerator extends Generator
{
    use DirectoriesGeneratorTrait;

    private $directories = [
        'src' => [
            'Data',
            'Domains',
            'Services',
            'Foundation',
            'Policies',
        ]
    ];

    public function generate($namespace)
    {
        $created = $this->generateDirectories();

        $this->generateCustomResources($namespace);

        return $created;
    }

    private function generateCustomResources($namespace)
    {
        $content = file_get_contents(__DIR__.'/stubs/foundation.serviceprovider.stub');
        $content = str_replace('{{namespace}}', $namespace, $content);

        if (!$this->exists($this->findSourceRoot().'/Foundation/ServiceProvider.php')) {
            $this->createFile($this->findSourceRoot().'/Foundation/ServiceProvider.php', $content);
            $this->delete($this->findSourceRoot().'/Foundation/.gitkeep');
        }
    }

}
