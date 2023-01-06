<?php

namespace Lucid\Events;

class FeatureStarted
{
    public string $name;

    public array $arguments;

    public function __construct(string $name, array $arguments = [])
    {
        $this->name = $name;
        $this->arguments = $arguments;
    }
}
