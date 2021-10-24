<?php

namespace Lucid;

use Illuminate\Support\Str as LaravelStr;

class Str
{
    /**
     * Convert a value to studly caps case.
     *
     * @param  string  $value
     * @return string
     */
    public static function studly($value)
    {
        return LaravelStr::studly($value);
    }

    /**
     * Convert a string to snake case.
     *
     * @param  string  $value
     * @param  string  $delimiter
     * @return string
     */
    public static function snake($value, $delimiter = '_')
    {
        return LaravelStr::snake($value, $delimiter);
    }

    /**
     * Determine the real name of the given name,
     * excluding the given pattern.
     * 	i.e. the name: "CreateArticleFeature.php" with pattern '/Feature.php'
     * 		will result in "Create Article".
     *
     * @param string $name
     * @param string $pattern
     *
     * @return string
     */
    public static function realName($name, $pattern = '//')
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
     *
     * @param string $name
     *
     * @return string
     */
    public static function feature($name)
    {
        $parts = array_map(function($part) { return self::studly($part); }, explode("/", $name));
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
     *
     * @param string $name
     *
     * @return string
     */
    public static function job($name)
    {
        return self::studly(preg_replace('/Job(\.php)?$/', '', $name).'Job');
    }

    /**
     * Get the given name formatted as an operation.
     *
     *  i.e. "Create Post Operation", "CreatePostOperation.php", "createPost",
     *  and many other forms will be transformed to "CreatePostOperation" which is
     *  the standard operation class name.
     *
     * @param string $name
     *
     * @return string
     */
    public static function operation($name)
    {
        return self::studly(preg_replace('/Operation(\.php)?$/', '', $name).'Operation');
    }

    /**
     * Get the given name formatted as a domain.
     *
     * Domain names are just CamelCase
     *
     * @param string $name
     *
     * @return string
     */
    public static function domain($name)
    {
        return self::studly($name);
    }

    /**
     * Get the given name formatted as a service name.
     *
     * @param string $name
     *
     * @return string
     */
    public static function service($name)
    {
        return self::studly($name);
    }

    /**
     * Get the given name formatted as a controller name.
     *
     * @param string $name
     *
     * @return string
     */
    public static function controller($name)
    {
        return self::studly(preg_replace('/Controller(\.php)?$/', '', $name).'Controller');
    }

    /**
     * Get the given name formatted as a model.
     *
     * Model names are just CamelCase
     *
     * @param string $name
     *
     * @return string
     */
    public static function model($name)
    {
        return self::studly($name);
    }

    /**
     * Get the given name formatted as a policy.
     *
     * @param $name
     * @return string
     */
    public static function policy($name)
    {
        return self::studly(preg_replace('/Policy(\.php)?$/', '', $name) . 'Policy');
    }

    /**
     * Get the given name formatted as a request.
     *
     * @param $name
     * @return string
     */
    public static function request($name)
    {
        return self::studly($name);
    }
}
