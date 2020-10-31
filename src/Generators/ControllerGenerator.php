<?php

namespace Lucid\Generators;

use Exception;
use Lucid\Str;

class ControllerGenerator extends Generator
{
    public function generate($name, $service, $plain = false)
    {
        $name = Str::controller($name);
        $service = Str::service($service);

        $path = $this->findControllerPath($service, $name);

        if ($this->exists($path)) {
            throw new Exception('Controller already exists!');

            return false;
        }

        $namespace = $this->findControllerNamespace($service);

        $content = file_get_contents($this->getStub($plain));
        $content = str_replace(
             ['{{controller}}', '{{namespace}}', '{{unit_namespace}}'],
             [$name, $namespace, $this->findUnitNamespace()],
             $content
         );

        $this->createFile($path, $content);

        return $this->relativeFromReal($path);
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub($plain)
    {
        if ($plain) {
            return __DIR__ . '/stubs/controller.plain.stub';
        }

        return __DIR__ . '/stubs/controller.stub';
    }
}
