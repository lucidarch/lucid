<?php

namespace Lucid\Testing;

class UnitMockRegistry
{
    /**
     * @var array
     */
    private $mocks = [];

    public function __construct()
    {
        // there should only be one instance of the registry,
        // so we register ourselves onto the application to be reused.
        // this is necessary in order to have a clean registry at the beginning of each test method run,
        // otherwise, with a singleton mocks will be carried on across test runs within the same class.
        app()->instance(static::class, $this);
    }

    public function has(string $unit): bool
    {
        return isset($this->mocks[$unit]);
    }

    public function get(string $unit): ?UnitMock
    {
        if (!$this->has($unit)) return null;

        return $this->mocks[$unit];
    }

    public function register(string $unit, UnitMock $mock)
    {
        $this->mocks[$unit] = $mock;
    }

    public function count()
    {
        return count($this->mocks);
    }
}
