<?php

namespace Lucid\Entities;

/**
 * @property-read string $title
 * @property-read string $className
 * @property-read Service $service
 * @property-read string $file
 * @property-read string $realPath
 * @property-read string $relativePath
 * @property-read string $content
 */
class Feature extends Entity
{
    public function __construct(
        string $title,
        string $file,
        string $realPath,
        string $relativePath,
        ?Service $service = null,
        string $content = ''
    ) {
        $this->setAttributes([
            'title' => $title,
            'className' => str_replace(' ', '', $title).'Feature',
            'service' => $service,
            'file' => $file,
            'realPath' => $realPath,
            'relativePath' => $relativePath,
            'content' => $content,
        ]);
    }
}
