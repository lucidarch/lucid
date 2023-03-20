<?php

namespace Lucid\Bus;

use App;
use Lucid\Testing\UnitMock;
use Lucid\Testing\UnitMockRegistry;
use ReflectionClass;
use Lucid\Units\Job;
use ReflectionException;
use Lucid\Units\Operation;
use Illuminate\Http\Request;
use Lucid\Events\JobStarted;
use Illuminate\Support\Collection;
use Lucid\Events\OperationStarted;
use Illuminate\Foundation\Bus\DispatchesJobs;

trait UnitDispatcher
{
    use Marshal;
    use DispatchesJobs;

    /**
     * decorator function to be called instead of the
     * laravel function dispatchFromArray.
     * When the $arguments is an instance of Request
     * it will call dispatchFrom instead.
     *
     * @param mixed $unit
     * @param array|\Illuminate\Http\Request $arguments
     * @param array $extra
     *
     * @return mixed
     */
    public function run($unit, $arguments = [], $extra = [])
    {

        /**
         * Laravel change the behaviour of the dispatch after the release of 10.0.0 and we have to explictly handle this run method
         * https://github.com/laravel/framework/commit/5f61fd1af0fa0b37a8888637578459eae21faeb
         * @author Nay Thu Khant (naythukhant644@gmail.com)
         *
         */
        $method = App::version() >= "10.0.0" ? "dispatchSync" : "dispatch";

        if (is_object($unit) && !App::runningUnitTests()) {
            $result = $this->{$method}($unit);
        } elseif ($arguments instanceof Request) {
            $result = $this->{$method}($this->marshal($unit, $arguments, $extra));
        } else {
            if (!is_object($unit)) {
                $unit = $this->marshal($unit, new Collection(), $arguments);

                // don't $this->dispatch() unit when in tests and have a mock for it.
            } elseif (App::runningUnitTests() && app(UnitMockRegistry::class)->has(get_class($unit))) {
                /** @var UnitMock $mock */
                $mock = app(UnitMockRegistry::class)->get(get_class($unit));
                $mock->compareTo($unit);

                // Reaching this step confirms that the expected mock is similar to the passed instance, so we
                // get the unit's mock counterpart to be $this->dispatch()ed. Otherwise, the previous step would
                // throw an exception when the mock doesn't match the passed instance.
                $unit = $this->marshal(
                    get_class($unit),
                    new Collection(),
                    $mock->getConstructorExpectationsForInstance($unit)
                );
            }

            $result = $this->{$method}($unit);
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
     * @param string $unit
     * @param array $arguments
     * @param string|null $queue
     *
     * @return mixed
     * @throws ReflectionException
     */
    public function runInQueue($unit, array $arguments = [], $queue = 'default')
    {
        // instantiate and queue the unit
        $reflection = new ReflectionClass($unit);
        $instance = $reflection->newInstanceArgs($arguments);
        $instance->onQueue((string)$queue);

        return $this->dispatch($instance);
    }
}
