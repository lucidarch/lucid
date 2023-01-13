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
     * @throws Exception
     */
    public function generate(string $name, string $domain): Request
    {
        $request = Str::request($name);
        $domain = Str::domain($domain);

        $path = $this->findRequestPath($domain, $request);

        if ($this->exists($path)) {
            throw new Exception('Request already exists');
        }

        $namespace = $this->findRequestsNamespace($domain);

        $content = file_get_contents($this->getStub());
        $content = str_replace(
            ['{{request}}', '{{namespace}}'],
            [$request, $namespace],
            $content ?: ''
        );

        $this->createFile($path, $content);

        return new Request(
            $request,
            $domain,
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
        return __DIR__ . '/../Generators/stubs/request.stub';
    }
}
