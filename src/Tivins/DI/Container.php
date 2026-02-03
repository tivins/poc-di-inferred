<?php

namespace Tivins\DI;

use Exception;
use ReflectionException;

class Container
{
    /**
     * Store singleton instances
     * @var array<string, object>
     */
    private array $container = [];

    /**
     * Store bindings (interface -> implementation)
     * @var array<string, class-string>
     */
    private array $bindings = [];

    public function __construct(private readonly ClassAnalyzer $analyzer)
    {
    }

    /**
     * Bind a class to an implementation
     * @param class-string $interface
     * @param class-string $implementation
     */
    public function bind(string $interface, string $implementation): void
    {
        $this->bindings[$interface] = $implementation;
    }
    /**
     * Get an instance of a class
     * @template T of object
     * @param class-string<T> $class
     * @return T
     * @throws ReflectionException
     * @throws Exception
     */
    public function get(string $class)
    {
        if (isset($this->bindings[$class])) {
            $class = $this->bindings[$class];
        }
        if (isset($this->container[$class])) {
            return $this->container[$class];
        }
        $instance = $this->instantiate($class);
        $this->container[$class] = $instance;
        return $instance;
    }

    /**
     * Remove a class from the container. 
     * 
     * **Important**: Because of the cache and the size of this library, this method will purge all the cache.
     * 
     * @param class-string $class
     */
    public function remove(string $class): void
    {
        $this->container = [];
        $this->bindings = [];
        $this->analyzer->purgeCache();
    }

    /**
     * Instantiate a class
     * @param class-string $class
     * @return object
     * @throws ReflectionException
     * @throws Exception
     */
    private function instantiate(string $class): object
    {
        $analysis = $this->analyzer->getConstructorAnalysis($class);

        if (!$analysis['hasConstructor']) {
            return new $class();
        }
        if (!$analysis['isInstantiable']) {
            throw new Exception("Class is not instantiable: " . $class);
        }
        if ($analysis['constructorPrivate']) {
            throw new Exception("Constructor is private: " . $class);
        }

        $dependencies = [];
        foreach ($analysis['parameters'] as $param) {
            if ($param['type'] === '') {
                throw new Exception("Parameter is not typed: " . $param['name']);
            }
            $dependencies[] = $this->get($param['type']);
        }

        return new $class(...$dependencies);
    }
}