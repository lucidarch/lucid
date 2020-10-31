<?php

namespace Lucid\Entities;

use Illuminate\Support\Str;

class Domain extends Entity
{
    public function __construct($name, $namespace, $path, $relativePath)
    {
        $this->setAttributes([
            'name' => $name,
            'slug' => Str::studly($name),
            'namespace' => $namespace,
            'realPath' => $path,
            'relativePath' => $relativePath,
        ]);
    }
}
