<?php

namespace Lucid\Entities;

use Lucid\Str;

/**
 * @property-read string name
 * @property-read string slug
 * @property-read string namespace
 * @property-read string realPath
 * @property-read string relativePath
 */
class Domain extends Entity
{
    public function __construct(
        string $name,
        string $namespace,
        string $path,
        string $relativePath
    ) {
        $this->setAttributes([
            'name' => $name,
            'slug' => Str::studly($name),
            'namespace' => $namespace,
            'realPath' => $path,
            'relativePath' => $relativePath,
        ]);
    }
}
