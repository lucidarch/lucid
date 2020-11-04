<?php


namespace Lucid\Entities;

class Request extends Entity
{
    public function __construct($title, $domain, $namespace, $file, $path, $relativePath, $content)
    {
        $this->setAttributes([
            'request' => $title,
            'domain' => $domain,
            'namespace' => $namespace,
            'file' => $file,
            'path' => $path,
            'relativePath' => $relativePath,
            'content' => $content,
        ]);
    }
}
