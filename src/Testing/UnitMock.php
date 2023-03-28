<?php

namespace Lucid\Testing;

use Exception;
use Lucid\Bus\Marshal;
use Mockery;
use Mockery\MockInterface;
use ReflectionClass;
use ReflectionException;

class UnitMock
{
    use Marshal;

    private string $unit;

    private array $constructorExpectations;

    private array $currentConstructorExpectations;

    private array $mocks = [];

    private MockInterface|Mockery\LegacyMockInterface|null $currentMock = null;

    /**
     * UnitMock constructor.
     */
    public function __construct(string $unit, array $constructorExpectations = [])
    {
        $this->unit = $unit;
        $this->setConstructorExpectations($constructorExpectations);
    }

    public function setConstructorExpectations(array $constructorExpectations): void
    {
        $this->currentConstructorExpectations = $constructorExpectations;
        $this->constructorExpectations[] = $this->currentConstructorExpectations;
    }

    public function getConstructorExpectations(): array
    {
        return $this->constructorExpectations;
    }

    /**
     * Returns constructor expectations array that matches the given $unit.
     * Empty array otherwise.
     *
     * @return array|mixed|void
     *
     * @throws ReflectionException
     */
    public function getConstructorExpectationsForInstance(mixed $unit)
    {
        foreach ($this->constructorExpectations as $index => $args) {
            $expected = new $unit(...$args);

            $ref = new ReflectionClass($unit);

            // we start by assuming that the unit instance and the $expected one are equal
            // until proven otherwise when we find differences between properties.
            $isEqual = true;
            foreach ($ref->getProperties() as $property) {
                if ($property->getValue($unit) !== $property->getValue($expected)) {
                    $isEqual = false;
                    break;
                }
            }

            if ($isEqual) {
                return $this->constructorExpectations[$index];
            }
        }
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    private function getCurrentConstructorArgs(): array
    {
        $args = [];

        $reflection = new ReflectionClass($this->unit);

        if ($constructor = $reflection->getConstructor()) {
            $args = array_map(function ($parameter) {
                return $this->getParameterValueForCommand(
                    $this->unit,
                    collect(),
                    $parameter,
                    $this->currentConstructorExpectations
                );
            }, $constructor->getParameters());
        }

        return $args;
    }

    /**
     * Register unit mock for current constructor expectations.
     *
     * @throws ReflectionException
     * @throws Exception
     */
    private function registerMock(): UnitMock
    {
        $this->currentMock = Mockery::mock("{$this->unit}[handle]", $this->getCurrentConstructorArgs());
        $this->mocks[] = $this->currentMock;

        // $args will be what the developer passed to the unit in actual execution
        app()->bind($this->unit, function ($app, $args) {
            foreach ($this->constructorExpectations as $key => $expectations) {
                if ($args == $expectations) {
                    return $this->mocks[$key];
                }
            }

            throw new Mockery\Exception\NoMatchingExpectationException(
                "\n\nExpected one of the following arguments sets for {$this->unit}::__construct(): ".
                print_r($this->constructorExpectations, true)."\nGot: ".
                print_r($args, true)
            );
        });

        return $this;
    }

    /**
     * Compare the mock to an actual instance.
     *
     * @throws Mockery\Exception\NoMatchingExpectationException
     */
    public function compareTo(object $unit): void
    {
        $expected = array_map(fn ($args) => new $unit(...$args), $this->constructorExpectations);

        $ref = new ReflectionClass($unit);
        foreach ($ref->getProperties() as $property) {
            $expectations = array_map(fn ($instance) => $property->getValue($instance), $expected);

            if (! in_array($property->getValue($unit), $expectations)) {
                throw new Mockery\Exception\NoMatchingExpectationException(
                    "Mismatch in \${$property->getName()} when running {$this->unit} \n\n--- Expected (one of)\n".
                    print_r(implode("\n", array_map(fn ($instance) => $property->getValue($instance), $expected)), true).
                    "\n\n+++Actual:\n".print_r($property->getValue($unit), true)."\n\n"
                );
            }
        }
    }

    /**
     * Compare the mock to an actual instance.
     *
     * @throws ReflectionException
     */
    public function getMock(): MockInterface|Mockery\LegacyMockInterface
    {
        $this->registerMock();

        return $this->currentMock;
    }

    /**
     * @throws ReflectionException
     */
    public function shouldBeDispatched(): void
    {
        $this->getMock()->shouldReceive('handle')->once();
    }

    /**
     * @throws ReflectionException
     */
    public function shouldNotBeDispatched(): void
    {
        if ($this->currentMock) {
            $this->getMock()->shouldNotReceive('handle');
        } else {
            $mock = Mockery::mock($this->unit)->makePartial();
            $mock->shouldNotReceive('handle');
            app()->bind($this->unit, function () use ($mock) {
                return $mock;
            });
        }
    }

    /**
     * @throws ReflectionException
     */
    public function shouldReturn(mixed $value): void
    {
        $this
            ->getMock()
            ->shouldReceive('handle')
            ->once()
            ->andReturn($value);
    }

    /**
     * @throws ReflectionException
     */
    public function shouldReturnTrue(): void
    {
        $this->shouldReturn(true);
    }

    /**
     * @throws ReflectionException
     */
    public function shouldReturnFalse(): void
    {
        $this->shouldReturn(false);
    }

    /**
     * @throws ReflectionException
     */
    public function shouldThrow(
        mixed $exception,
        string $message = '',
        int $code = 0,
        Exception $previous = null
    ): void {
        $this
            ->getMock()
            ->shouldReceive('handle')
            ->once()
            ->andThrow($exception, $message, $code, $previous);
    }
}
