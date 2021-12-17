<?php

namespace Lucid\Bus;

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
     * @param mixed                         $unit
     * @param array|\Illuminate\Http\Request $arguments
     * @param array                          $extra
     *
     * @return mixed
     */
    public function run($unit, $arguments = [], $extra = [])
    {
        if ($arguments instanceof Request) {
            $result = $this->dispatch($this->marshal($unit, $arguments, $extra));
        } else {
            if (!is_object($unit)) {
                $unit = $this->marshal($unit, new Collection(), $arguments);
            }

            if ($unit instanceof Operation) {
                event(new OperationStarted(get_class($unit), $arguments));
            }

            if ($unit instanceof Job) {
                event(new JobStarted(get_class($unit), $arguments));
            }

            $result = $this->dispatch($unit, $arguments);
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
        $instance->onQueue((string) $queue);

        return $this->dispatch($instance);
    }
}
