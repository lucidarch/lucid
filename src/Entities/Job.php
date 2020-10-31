<?php

namespace Lucid\Entities;

class Job extends Entity
{
    public function __construct($title, $namespace, $file, $path, $relativePath, Domain $domain = null, $content = '')
    {
        $className = str_replace(' ', '', $title).'Job';
        $this->setAttributes([
            'title' => $title,
            'className' => $className,
            'namespace' => $namespace,
            'file' => $file,
            'realPath' => $path,
            'relativePath' => $relativePath,
            'domain' => $domain,
            'content' => $content,
        ]);
    }

    public function toArray()
    {
        $attributes = parent::toArray();

        if ($attributes['domain'] instanceof Domain) {
            $attributes['domain'] = $attributes['domain']->toArray();
        }

        return $attributes;
    }
}
