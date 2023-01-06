<?php

namespace Lucid\Events;

class OperationStarted
{
    public string $name;

    public array $arguments;

    public function __construct(string $name, array $arguments = [])
    {
        $this->name = $name;
        $this->arguments = $arguments;
    }
}
