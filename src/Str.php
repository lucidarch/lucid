<?php

namespace Lucid;

use Illuminate\Support\Str as LaravelStr;

class Str
{
    /**
     * Convert a value to studly caps case.
     */
    public static function studly(?string $value): string
    {
        return LaravelStr::studly($value ?? '');
    }

    /**
     * Convert a string to snake case.
     */
    public static function snake(string $value, string $delimiter = '_'): string
    {
        return LaravelStr::snake($value, $delimiter);
    }

    /**
     * Replace last occurrence of string inside another string with specified string
     */
    public static function replaceLast(
        string $search,
        string $replace,
        string $subject
    ): string
    {
        return LaravelStr::replaceLast($search, $replace, $subject);
    }

    /**
     * Force sending $subject as string to avoid warnings
     */
    public static function replace(
        array|string $search,
        array|string $replace,
        string $subject
    ): string
    {
        return str_replace($search, $replace, $subject);
    }

    public static function substr(
        string $string,
        int $offset
    ): string
    {
        $substr = substr($string, $offset);

        if ($substr === false) {
            return '';
        }

        return $substr;
    }

    /**
     * Determine the real name of the given name,
     * excluding the given pattern.
     * 	i.e. the name: "CreateArticleFeature.php" with pattern '/Feature.php'
     * 		will result in "Create Article".
     */
    public static function realName(string $name, string $pattern = '//'): string
    {
        $name = preg_replace($pattern, '', $name);

        return implode(' ', preg_split('/(?=[A-Z])/', $name, -1, PREG_SPLIT_NO_EMPTY));
    }

    /**
     * Get the given name formatted as a feature.
     *
     * 	i.e. "Create Post Feature", "CreatePostFeature.php", "createPost", "createe"
     * 	and many other forms will be transformed to "CreatePostFeature" which is
     * 	the standard feature class name.
     */
    public static function feature(?string $name): string
    {
        $parts = array_map(function($part) { return self::studly($part); }, explode("/", $name ?? ''));
        $feature  = self::studly(preg_replace('/Feature(\.php)?$/', '', array_pop($parts)).'Feature');

        $parts[] = $feature;

        return join(DS, $parts);
    }

    /**
     * Get the given name formatted as a job.
     *
     * 	i.e. "Create Post Feature", "CreatePostJob.php", "createPost",
     * 	and many other forms will be transformed to "CreatePostJob" which is
     * 	the standard job class name.
     */
    public static function job(string $name): string
    {
        return self::studly(preg_replace('/Job(\.php)?$/', '', $name).'Job');
    }

    /**
     * Get the given name formatted as an operation.
     *
     *  i.e. "Create Post Operation", "CreatePostOperation.php", "createPost",
     *  and many other forms will be transformed to "CreatePostOperation" which is
     *  the standard operation class name.
     */
    public static function operation(string $name): string
    {
        return self::studly(preg_replace('/Operation(\.php)?$/', '', $name).'Operation');
    }

    /**
     * Get the given name formatted as a domain.
     *
     * Domain names are just CamelCase
     */
    public static function domain(string $name): string
    {
        return self::studly($name);
    }

    /**
     * Get the given name formatted as a service name.
     */
    public static function service(?string $name): string
    {
        return self::studly($name ?? '');
    }

    /**
     * Get the given name formatted as a controller name.
     */
    public static function controller(string $name): string
    {
        return self::studly(preg_replace('/Controller(\.php)?$/', '', $name).'Controller');
    }

    /**
     * Get the given name formatted as a model.
     *
     * Model names are just CamelCase
     */
    public static function model(string $name): string
    {
        return self::studly($name);
    }

    /**
     * Get the given name formatted as a policy.
     */
    public static function policy(string $name): string
    {
        return self::studly(preg_replace('/Policy(\.php)?$/', '', $name) . 'Policy');
    }

    /**
     * Get the given name formatted as a request.
     */
    public static function request(string $name): string
    {
        return self::studly($name);
    }
}
