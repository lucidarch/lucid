<?php

namespace Lucid\Bus;

use Illuminate\Support\Collection;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Lucid\Events\FeatureStarted;

trait ServesFeatures
{
    use Marshal;
    use DispatchesJobs;

    /**
     * Serve the given feature with the given arguments.
     *
     * @param string $feature
     * @param array $arguments
     *
     * @return mixed
     */

    public function serve($feature, $arguments = [])
    {
        /**
         * Laravel change the behaviour of the dispatch after the release of 10.0.0 and we have to explictly handle this run method
         * https://github.com/laravel/framework/commit/5f61fd1af0fa0b37a8888637578459eae21faeb
         * @author Nay Thu Khant (naythukhant644@gmail.com)
         *
         */
        $method = App::version() >= "10.0.0" ? "dispatchSync" : "dispatch";

        event(new FeatureStarted($feature, $arguments));

        return $this->{$method}($this->marshal($feature, new Collection(), $arguments));
    }
}
