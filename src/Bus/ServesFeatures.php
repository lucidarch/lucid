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
     */
    public function serve(string $feature, array $arguments = []): mixed
    {
        event(new FeatureStarted($feature, $arguments));

        return $this->dispatch($this->marshal($feature, new Collection(), $arguments));
    }
}
