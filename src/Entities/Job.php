<?php

namespace Lucid\Entities;

/**
 * @property-read string title
 * @property-read string className
 * @property-read string namespace
 * @property-read string file
 * @property-read string realPath
 * @property-read string relativePath
 * @property-read string domain
 * @property-read string content
 */
class Job extends Entity
{
    public function __construct(
        string $title,
        string $namespace,
        string $file,
        string $path,
        string $relativePath,
        ?Domain $domain = null,
        string $content = ''
    )
    {
        $this->setAttributes([
            'title' => $title,
            'className' => str_replace(' ', '', $title) . 'Job',
            'namespace' => $namespace,
            'file' => $file,
            'realPath' => $path,
            'relativePath' => $relativePath,
            'domain' => $domain,
            'content' => $content,
        ]);
    }

    public function toArray(): array
    {
        $attributes = parent::toArray();

        if ($attributes['domain'] instanceof Domain) {
            $attributes['domain'] = $attributes['domain']->toArray();
        }

        return $attributes;
    }
}
