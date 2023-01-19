<?php

namespace Lucid\Entities;

/**
 * @property-read string model
 * @property-read string namespace
 * @property-read string file
 * @property-read string path
 * @property-read string relativePath
 * @property-read string content
 */
class Model extends Entity
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
            'model' => $title,
            'namespace' => $namespace,
            'file' => $file,
            'path' => $path,
            'relativePath' => $relativePath,
            'content' => $content,
        ]);
    }
}
