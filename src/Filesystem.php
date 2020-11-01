<?php

namespace Lucid;

use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

trait Filesystem
{
    /**
     * Determine if a file or directory exists.
     *
     * @param string $path
     *
     * @return bool
     */
    public function exists($path)
    {
        return file_exists($path);
    }

    /**
     * Create a file at the given path with the given contents.
     *
     * @param string $path
     * @param string $contents
     *
     * @return bool
     */
    public function createFile($path, $contents = '', $lock = false)
    {
        $this->createDirectory(dirname($path));

        return file_put_contents($path, $contents, $lock ? LOCK_EX : 0);
    }

    /**
     * Create a directory.
     *
     * @param string $path
     * @param int    $mode
     * @param bool   $recursive
     * @param bool   $force
     *
     * @return bool
     */
    public function createDirectory($path, $mode = 0755, $recursive = true, $force = true)
    {
        if ($force) {
            return @mkdir($path, $mode, $recursive);
        }

        return mkdir($path, $mode, $recursive);
    }

    /**
     * Delete an existing file or directory at the given path.
     *
     * @param string $path
     *
     * @return bool
     */
    public function delete($path)
    {
        $filesystem = new SymfonyFilesystem();

        $filesystem->remove($path);
    }

    public function rename($path, $name)
    {
        $filesystem = new SymfonyFilesystem();

        $filesystem->rename($path, $name);
    }
}
