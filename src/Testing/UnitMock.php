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

    /**
     * @var string $unit
     */
    private $unit;

    /**
     * @var array $constructorExpectations
     */
    private $constructorExpectations;

    /**
     * @var array $currentConstructorExpectations
     */
    private $currentConstructorExpectations;

    /**
     * @var array $mocks
     */
    private $mocks;

    /**
     * @var MockInterface $currentMock
     */
    private $currentMock;

    /**
     * UnitMock constructor.
     *
     * @param  string  $unit
     * @param  array  $constructorExpectations
     */
    public function __construct(string $unit, array $constructorExpectations = [])
    {
        $this->unit = $unit;
        $this->setConstructorExpectations($constructorExpectations);
    }

    public function setConstructorExpectations(array $constructorExpectations)
    {
        $this->currentConstructorExpectations = $constructorExpectations;
        $this->constructorExpectations[] = $this->currentConstructorExpectations;
    }

    /**
     * @return array
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
     * @return $this
     * @throws ReflectionException
     * @throws Exception
     */
    private function registerMock(): UnitMock
    {
        $this->currentMock = Mockery::mock("{$this->unit}[handle]", $this->getCurrentConstructorArgs());
        $this->mocks[] = $this->currentMock;

        app()->beforeResolving($this->unit, function ($app, $args) {

            foreach ($this->constructorExpectations as $key => $expectations) {
                if ($args == $expectations) {
                    app()->bind($this->unit, function () use ($key) {
                        return $this->mocks[$key];
                    });
                    $bound = true;
                    break;
                }
            }

            if (!isset($bound) && empty($args)) {
                throw new Mockery\Exception\NoMatchingExpectationException(
                    "\n\nExpected one of the following arguments sets for {$this->unit}::__construct(): " .
                    print_r($this->constructorExpectations, true) . "\nGot: " .
                    print_r($args, true)
                );
            }
        });

        return $this;
    }

    public function getMock(): MockInterface
    {
        $this->registerMock();

        return $this->currentMock;
    }

    public function shouldBeDispatched()
    {
        $this->getMock()->shouldReceive('handle')->once();
    }

    public function shouldNotBeDispatched()
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

    public function shouldReturn($value)
    {
        $this->getMock()->shouldReceive('handle')->once()->andReturn($value);
    }

    public function shouldReturnTrue()
    {
        $this->shouldReturn(true);
    }

    public function shouldReturnFalse()
    {
        $this->shouldReturn(false);
    }

    public function shouldThrow($exception, $message = '', $code = 0, Exception $previous = null)
    {
        $this->getMock()->shouldReceive('handle')
            ->once()
            ->andThrow($exception, $message, $code, $previous);
    }
}
