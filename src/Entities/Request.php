<?php


namespace Lucid\Entities;

class Request extends Entity
{
    public function __construct($title, $service, $namespace, $file, $path, $relativePath, $content)
    {
        $this->setAttributes([
            'request' => $title,
            'service' => $service,
            'namespace' => $namespace,
            'file' => $file,
            'path' => $path,
            'relativePath' => $relativePath,
            'content' => $content,
        ]);
    }
}
