<?php

namespace Lucid\Generators;

use Exception;
use Lucid\Str;

class ControllerGenerator extends Generator
{
    /**
     * @throws Exception
     */
    public function generate(
        string $name,
        ?string $service,
        bool $resource = false
    ): string {
        $name = Str::controller($name);
        $service = Str::service($service);

        $path = $this->findControllerPath($service, $name);

        if ($this->exists($path)) {
            throw new Exception('Controller already exists!');
        }

        $namespace = $this->findControllerNamespace($service);

        $content = file_get_contents($this->getStub($resource));
        $content = str_replace(
            ['{{controller}}', '{{namespace}}', '{{unit_namespace}}'],
            [$name, $namespace, $this->findUnitNamespace()],
            $content ?: ''
        );

        $this->createFile($path, $content);

        return $this->relativeFromReal($path);
    }

    /**
     * Get the stub file for the generator.
     *
     * $resource - Determines whether to return the resource controller
     */
    protected function getStub(bool $resource): string
    {
        if ($resource) {
            return __DIR__.'/stubs/controller.resource.stub';
        }

        return __DIR__.'/stubs/controller.plain.stub';
    }
}
