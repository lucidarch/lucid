<?php

namespace Lucid;

use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;

trait Filesystem
{
    /**
     * Determine if a file or directory exists.
     */
    public function exists(string $path): bool
    {
        return file_exists($path);
    }

    /**
     * Create a file at the given path with the given contents.
     */
    public function createFile(
        string $path,
        string $contents = '',
        bool $lock = false
    ): bool {
        $this->createDirectory(dirname($path));

        return file_put_contents($path, $contents, $lock ? LOCK_EX : 0);
    }

    /**
     * Create a directory.
     */
    public function createDirectory(
        string $path,
        int $mode = 0755,
        bool $recursive = true,
        bool $force = true
    ): bool {
        if ($force) {
            return @mkdir($path, $mode, $recursive);
        }

        return mkdir($path, $mode, $recursive);
    }

    /**
     * Delete an existing file or directory at the given path.
     */
    public function delete(string $path): void
    {
        $filesystem = new SymfonyFilesystem();

        $filesystem->remove($path);
    }

    /**
     * Rename file at specified path.
     */
    public function rename(string $path, string $name): void
    {
        $filesystem = new SymfonyFilesystem();

        $filesystem->rename($path, $name);
    }
}
