<?php

namespace Lucid\Testing;

trait MockMe
{
    public static function mock(array $constructorExpectations = []): UnitMock
    {
        $unit = static::class;

        if (UnitMockRegistry::has($unit)) {
            $mock = UnitMockRegistry::get($unit);
            $mock->setConstructorExpectations($constructorExpectations);
        } else {
            $mock = new UnitMock($unit, $constructorExpectations);
            UnitMockRegistry::register($unit, $mock);
        }

        return $mock;
    }
}
