<?php

namespace Lucid\Entities;

/**
 * @property-read string request
 * @property-read string domain
 * @property-read string namespace
 * @property-read string file
 * @property-read string path
 * @property-read string relativePath
 * @property-read string content
 */
class Request extends Entity
{
    public function __construct(
        string $title,
        string $domain,
        string $namespace,
        string $file,
        string $path,
        string $relativePath,
        string $content
    )
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
