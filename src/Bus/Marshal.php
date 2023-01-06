<?php

namespace Lucid\Bus;

use Exception;
use ArrayAccess;
use ReflectionParameter;

trait Marshal
{
    /**
     * Marshal a command from the given array accessible object.
     */
    protected function marshal(
        string $command,
        ArrayAccess $source,
        array $extras = []
    ): mixed
    {
        $parameters = [];

        foreach ($source as $name => $parameter) {
            $parameters[$name] = $parameter;
        }

        $parameters = array_merge($parameters, $extras);

        return app($command, $parameters);
    }

    /**
     * Get a parameter value for a marshaled command.
     *
     * @throws Exception
     */
    protected function getParameterValueForCommand(
        string $command,
        ArrayAccess $source,
        ReflectionParameter $parameter,
        array $extras = []
    ): mixed
    {
        if (array_key_exists($parameter->name, $extras)) {
            return $extras[$parameter->name];
        }

        if (isset($source[$parameter->name])) {
            return $source[$parameter->name];
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new Exception("Unable to map parameter [{$parameter->name}] to command [{$command}]");
    }
}
