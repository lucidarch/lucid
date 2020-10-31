<?php

namespace Lucid\Entities;

use Lucid\Str;

class Service extends Entity
{
    public function __construct($name, $realPath, $relativePath)
    {
        $this->setAttributes([
            'name' => $name,
            'slug' => Str::snake($name),
            'realPath' => $realPath,
            'relativePath' => $relativePath,
        ]);
    }

    // public function toArray()
    // {
    //     $attributes = parent::toArray();
    //
    //     unset($attributes['realPath']);
    //
    //     return $attributes;
    // }
}
