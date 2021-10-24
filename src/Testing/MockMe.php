<?php

namespace Lucid\Testing;

use Mockery\Mock;

trait MockMe
{
    public static function mock(array $constructorExpectations = []): UnitMock
    {
        $unit = static::class;

        /** @var UnitMockRegistry $registry */
        $registry = app(UnitMockRegistry::class);

        if ($registry->has($unit)) {
            $mock = $registry->get($unit);
            $mock->setConstructorExpectations($constructorExpectations);
        } else {
            $mock = new UnitMock($unit, $constructorExpectations);
            $registry->register($unit, $mock);
        }

        return $mock;
    }
}
