<?php

namespace Lucid\Bus;

use App;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Lucid\Events\JobStarted;
use Lucid\Events\OperationStarted;
use Lucid\Testing\UnitMock;
use Lucid\Testing\UnitMockRegistry;
use Lucid\Units\Job;
use Lucid\Units\Operation;
use ReflectionClass;
use ReflectionException;

trait UnitDispatcher
{
    use Marshal;
    use DispatchesJobs;

    /**
     * decorator function to be called instead of the
     * laravel function dispatchFromArray.
     * When the $arguments is an instance of Request
     * it will call dispatchFrom instead.
     */
    public function run(
        mixed $unit,
        array|Request $arguments = [],
        array $extra = []
    ): mixed {
        if (is_object($unit) && ! App::runningUnitTests()) {
            $result = $this->dispatchSync($unit);
        } elseif ($arguments instanceof Request) {
            $result = $this->dispatchSync($this->marshal($unit, $arguments, $extra));
        } else {
            if (! is_object($unit)) {
                $unit = $this->marshal($unit, new Collection(), $arguments);

            // don't dispatch unit when in tests and have a mock for it.
            } elseif (App::runningUnitTests() && app(UnitMockRegistry::class)->has(get_class($unit))) {
                /** @var UnitMock $mock */
                $mock = app(UnitMockRegistry::class)->get(get_class($unit));
                $mock->compareTo($unit);

                // Reaching this step confirms that the expected mock is similar to the passed instance, so we
                // get the unit's mock counterpart to be dispatched. Otherwise, the previous step would
                // throw an exception when the mock doesn't match the passed instance.
                $unit = $this->marshal(
                    get_class($unit),
                    new Collection(),
                    $mock->getConstructorExpectationsForInstance($unit)
                );
            }

            $result = $this->dispatchSync($unit);
        }

        if ($unit instanceof Operation) {
            event(new OperationStarted(get_class($unit), $arguments));
        }

        if ($unit instanceof Job) {
            event(new JobStarted(get_class($unit), $arguments));
        }

        return $result;
    }

    /**
     * Run the given unit in the given queue.
     *
     * @throws ReflectionException
     */
    public function runInQueue(
        string $unit,
        array $arguments = [],
        ?string $queue = 'default'
    ): mixed {
        // instantiate and queue the unit
        $reflection = new ReflectionClass($unit);
        $instance = $reflection->newInstanceArgs($arguments);
        $instance->onQueue((string) $queue);

        return $this->dispatch($instance);
    }
}
