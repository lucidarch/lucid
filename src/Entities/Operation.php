<?php

namespace Lucid\Entities;

class Operation extends Entity
{
    public function __construct($title, $file, $realPath, $relativePath, Service $service = null, $content = '')
    {
        $className = str_replace(' ', '', $title).'Operation';

        $this->setAttributes([
            'title' => $title,
            'className' => $className,
            'service' => $service,
            'file' => $file,
            'realPath' => $realPath,
            'relativePath' => $relativePath,
            'content' => $content,
        ]);
    }
}
