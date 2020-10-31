<?php

namespace Lucid\Entities;

use Illuminate\Contracts\Support\Arrayable;

/**
 * @property-read string title
 * @property-read string className
 * @property-read string service
 * @property-read string file
 * @property-read string realPath
 * @property-read string relativePath
 * @property-read string content
 */
class Entity implements Arrayable
{
    protected $attributes = [];

    /**
     * Get the array representation of this instance.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }

    /**
     * Set the attributes for this component.
     *
     * @param array $attributes
     */
    protected function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Get an attribute's value if found.
     *
     * @param  string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        if (isset($this->attributes[$key])) {
            return $this->attributes[$key];
        }
    }

}
