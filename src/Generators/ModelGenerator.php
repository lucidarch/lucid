<?php

namespace Lucid\Generators;

use Exception;
use Lucid\Str;
use Lucid\Entities\Model;

class ModelGenerator extends Generator
{
    /**
     * Generate the file.
     *
     * @throws Exception
     */
    public function generate(string $name): Model
    {
        $model = Str::model($name);
        $path = $this->findModelPath($model);

        if ($this->exists($path)) {
            throw new Exception('Model already exists');
        }

        $namespace = $this->findModelNamespace();

        $content = file_get_contents($this->getStub());
        $content = Str::replace(
            ['{{model}}', '{{namespace}}', '{{unit_namespace}}'],
            [$model, $namespace, $this->findUnitNamespace()],
            $content
        );

        $this->createFile($path, $content);

        return new Model(
            $model,
            $namespace,
            basename($path),
            $path,
            $this->relativeFromReal($path),
            $content
        );
    }

    /**
     * Get the stub file for the generator.
     */
    public function getStub(): string
    {
        return __DIR__ . '/../Generators/stubs/model-8.stub';
    }
}
