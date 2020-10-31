<?php


namespace Lucid\Generators;

use Exception;
use Lucid\Str;
use Lucid\Entities\Request;

class RequestGenerator extends Generator
{
    /**
     * Generate the file.
     *
     * @param string $name
     * @param string $service
     * @return Request|bool
     * @throws Exception
     */
    public function generate($name, $service)
    {
        $request = Str::request($name);
        $service = Str::service($service);
        $path = $this->findRequestPath($service, $request);

        if ($this->exists($path)) {
            throw new Exception('Request already exists');

            return false;
        }

        $namespace = $this->findRequestsNamespace($service);

        $content = file_get_contents($this->getStub());
        $content = str_replace(
            ['{{request}}', '{{namespace}}'],
            [$request, $namespace],
            $content
        );

        $this->createFile($path, $content);

        return new Request(
            $request,
            $service,
            $namespace,
            basename($path),
            $path,
            $this->relativeFromReal($path),
            $content
        );
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    public function getStub()
    {
        return __DIR__ . '/../Generators/stubs/request.stub';
    }
}
