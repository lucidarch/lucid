<?php

namespace Lucid\Entities;

use Lucid\Str;

/**
 * @property-read string $name
 * @property-read string $slug
 * @property-read string $realPath
 * @property-read string $relativePath
 */
class Service extends Entity
{
    public function __construct(string $name, string $realPath, string $relativePath)
    {
        $this->setAttributes([
            'name' => $name,
            'slug' => Str::snake($name),
            'realPath' => $realPath,
            'relativePath' => $relativePath,
        ]);
    }
}
