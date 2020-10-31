<?php

namespace Lucid\Bus;

use Illuminate\Support\Collection;
use Lucid\Bus\MarshalTrait;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Lucid\Events\FeatureStarted;

trait ServesFeaturesTrait
{
    use MarshalTrait;
    use DispatchesJobs;

    /**
     * Serve the given feature with the given arguments.
     *
     * @param string $feature
     * @param array  $arguments
     *
     * @return mixed
     */
    public function serve($feature, $arguments = [])
    {
        event(new FeatureStarted($feature, $arguments));

        return $this->dispatch($this->marshal($feature, new Collection(), $arguments));
    }
}
