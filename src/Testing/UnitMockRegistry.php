<?php

namespace Lucid\Testing;

class UnitMockRegistry
{
    /**
     * @var array
     */
    private static $mocks = [];

    public static function has(string $unit): bool
    {
        return isset(self::$mocks[$unit]);
    }

    public static function get(string $unit): ?UnitMock
    {
        if (!self::has($unit)) return null;

        return self::$mocks[$unit];
    }

    public static function register(string $unit, UnitMock $mock)
    {
        self::$mocks[$unit] = $mock;
    }
}
