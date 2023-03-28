<?php

namespace Lucid\Generators;

use Exception;
use Lucid\Entities\Service;
use Lucid\Str;

class ServiceGenerator extends Generator
{
    protected array $directories = [
        'Console/',
        'database/',
        'database/factories/',
        'database/migrations/',
        'database/seeders/',
        'Http/',
        'Http/Controllers/',
        'Http/Middleware/',
        'Providers/',
        'Features/',
        'Operations/',
        'resources/',
        'resources/lang/',
        'resources/views/',
        'routes',
        'Tests/',
        'Tests/Features/',
        'Tests/Operations/',
    ];

    /**
     * Add the corresponding service provider for the created service.
     *
     * @throws Exception
     */
    public function generate(string $name): Service
    {
        $name = Str::service($name);
        $slug = Str::snake($name);
        $path = $this->findServicePath($name);

        if ($this->exists($path)) {
            throw new Exception('Service already exists!');
        }

        // create service directory
        $this->createDirectory($path);
        // create .gitkeep file in it
        $this->createFile($path.'/.gitkeep');

        $this->createServiceDirectories($path);

        $this->addServiceProviders($name, $slug, $path);

        $this->addRoutesFiles($name, $slug, $path);

        $this->addWelcomeViewFile($path);

        return new Service(
            $name,
            $path,
            $this->relativeFromReal($path)
        );
    }

    /**
     * Create the default directories at the given service path.
     */
    public function createServiceDirectories(string $path): void
    {
        foreach ($this->directories as $directory) {
            $this->createDirectory($path.'/'.$directory);
            $this->createFile($path.'/'.$directory.'/.gitkeep');
        }
    }

    /**
     * Add the corresponding service provider for the created service.
     *
     * @throws Exception
     */
    public function addServiceProviders(
        string $name,
        string $slug,
        string $path
    ): void {
        $namespace = $this->findServiceNamespace($name).'\\Providers';

        $this->createRegistrationServiceProvider($name, $path, $slug, $namespace);

        $this->createRouteServiceProvider($name, $path, $slug, $namespace);

        $this->createBroadcastServiceProvider($name, $path, $slug, $namespace);
    }

    /**
     * Create the service provider that registers broadcast channels.
     */
    public function createBroadcastServiceProvider(
        string $name,
        string $path,
        string $slug,
        string $namespace
    ): void {
        $content = file_get_contents(__DIR__.'/stubs/broadcastserviceprovider.stub');
        $content = str_replace(
            ['{{name}}', '{{slug}}', '{{namespace}}'],
            [$name, $slug, $namespace],
            $content
        );

        $this->createFile($path.'/Providers/BroadcastServiceProvider.php', $content);
    }

    /**
     * Create the service provider that registers this service.
     */
    public function createRegistrationServiceProvider(
        string $name,
        string $path,
        string $slug,
        string $namespace
    ): void {
        $content = file_get_contents(__DIR__.'/stubs/serviceprovider.stub');
        $content = str_replace(
            ['{{name}}', '{{slug}}', '{{namespace}}'],
            [$name, $slug, $namespace],
            $content
        );

        $this->createFile($path.'/Providers/'.$name.'ServiceProvider.php', $content);
    }

    /**
     * Create the routes service provider file.
     *
     * @throws Exception
     */
    public function createRouteServiceProvider(
        string $name,
        string $path,
        string $slug,
        string $namespace
    ): void {
        $serviceNamespace = $this->findServiceNamespace($name);
        $controllers = $serviceNamespace.'\Http\Controllers';
        $foundation = $this->findUnitNamespace();

        $content = file_get_contents(__DIR__.'/stubs/routeserviceprovider.stub');
        $content = str_replace(
            ['{{name}}', '{{namespace}}', '{{controllers_namespace}}', '{{unit_namespace}}'],
            [$name, $namespace, $controllers, $foundation],
            $content
        );

        $this->createFile($path.'/Providers/RouteServiceProvider.php', $content);
    }

    /**
     * Add the routes files.
     *
     * @throws Exception
     */
    public function addRoutesFiles(string $name, string $slug, string $path): void
    {
        $controllers = 'src/Services/'.$name.'/Http/Controllers';

        $api = file_get_contents(__DIR__.'/stubs/routes-api.stub');
        $api = str_replace(['{{slug}}', '{{controllers_path}}'], [$slug, $controllers], $api);

        $web = file_get_contents(__DIR__.'/stubs/routes-web.stub');
        $web = str_replace(['{{slug}}', '{{controllers_path}}'], [$slug, $controllers], $web);

        $channels = file_get_contents(__DIR__.'/stubs/routes-channels.stub');
        $channels = str_replace(['{{namespace}}'], [$this->findServiceNamespace($name)], $channels);

        $console = file_get_contents(__DIR__.'/stubs/routes-console.stub');

        $this->createFile($path.'/routes/api.php', $api);
        $this->createFile($path.'/routes/web.php', $web);
        $this->createFile($path.'/routes/channels.php', $channels);
        $this->createFile($path.'/routes/console.php', $console);

        unset($api, $web, $channels, $console);

        $this->delete($path.'/routes/.gitkeep');
    }

    /**
     * Add the welcome view file.
     */
    public function addWelcomeViewFile(string $path): void
    {
        $this->createFile(
            $path.'/resources/views/welcome.blade.php',
            file_get_contents(__DIR__.'/stubs/welcome.blade.stub')
        );
    }

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        return __DIR__.'/stubs/service.stub';
    }
}
