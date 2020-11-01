<?php

namespace Lucid\Generators;

/**
 * Hosts common directory creation based on $directories array
 * in the using class.
 *
 * Depends on Finder and Filesystem traits.
 */
trait DirectoriesGeneratorTrait
{
    /**
     * @return array
     */
    private function generateDirectories()
    {
        $root = base_path();

        // create directories
        $created = [];
        foreach ($this->directories as $parent => $children) {
            $paths = array_map(function ($child) use ($root, $parent) {
                return "$root/$parent/$child";
            }, $children);

            foreach ($paths as $path) {
                $this->createDirectory($path);
                $this->createFile("$path/.gitkeep");
                // collect path without root
                $created[] = str_replace($root, '', $path);
            }
        }

        return $created;
    }
}
