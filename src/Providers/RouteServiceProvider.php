<?php

namespace Lucid\Providers;

use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as BaseServiceProvider;

abstract class RouteServiceProvider extends BaseServiceProvider
{
    /**
     * Read the routes from the "api.php" and "web.php" files of this Service
     *
     * @param Router $router
     */
    abstract public function map(Router $router);

    /**
     * @param $router
     * @param $namespace
     * @param $pathApi
     * @param $pathWeb
     */
    public function loadRoutesFiles($router, $namespace, $pathApi = null, $pathWeb = null)
    {
        if (is_file($pathApi)) {
            $this->mapApiRoutes($router, $namespace, $pathApi);
        }
        if (is_file($pathWeb)) {
            $this->mapWebRoutes($router, $namespace, $pathWeb);
        }
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @param $router
     * @param $namespace
     * @param $path
     * @param $prefix
     *
     * @return void
     */
    protected function mapApiRoutes($router, $namespace, $path, $prefix='api')
    {
        $router->group([
            'middleware' => 'api',
            'namespace'  => $namespace,
            'prefix'     => $prefix // to allow the delete or change of api prefix
        ], function ($router) use ($path) {
            require $path;
        });
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @param $router
     * @param $namespace
     * @param $path
     *
     * @return void
     */
    protected function mapWebRoutes($router, $namespace, $path)
    {
        $router->group([
            'middleware' => 'web',
            'namespace'  => $namespace
        ], function ($router) use ($path) {
            require $path;
        });
    }
}
