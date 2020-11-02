<?php

namespace Lucid\Generators;

use Lucid\Finder;
use Lucid\Filesystem;

class Generator
{
    use Finder;
    use Filesystem;

    /**
     * The current Laravel framework version
     * as defined in Foundation\Application::VERSION
     *
     * @param bool $majorOnly determines whether the needed version is only the major one
     * @return string
     */
    public function laravelVersion($majorOnly = true)
    {
        $version = app()->version();
        if ($majorOnly) {
            $version = explode('.', $version)[0];
        }

        return $version;
    }
}
