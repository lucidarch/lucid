<?php

namespace Lucid\Entities;

/**
 * @property-read string $policy
 * @property-read string $namespace
 * @property-read string $file
 * @property-read string $path
 * @property-read string $relativePath
 * @property-read string $content
 */
class Policy extends Entity
{
    public function __construct(
        string $title,
        string $namespace,
        string $file,
        string $path,
        string $relativePath,
        string $content
    ) {
        $this->setAttributes([
            'policy' => $title,
            'namespace' => $namespace,
            'file' => $file,
            'path' => $path,
            'relativePath' => $relativePath,
            'content' => $content,
        ]);
    }
}
