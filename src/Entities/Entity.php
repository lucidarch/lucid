<?php

namespace Lucid\Entities;

use Illuminate\Contracts\Support\Arrayable;

class Entity implements Arrayable
{
    protected array $attributes = [];

    /**
     * Get the array representation of this instance.
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * Set the attributes for this component.
     */
    protected function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    /**
     * Get an attribute's value if found.
     */
    public function __get(string $key): mixed
    {
        if (isset($this->attributes[$key])) {
            return $this->attributes[$key];
        }

        return null;
    }
}
