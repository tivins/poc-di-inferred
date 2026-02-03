<?php

namespace Tivins\DI;

use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

class Container
{
    /**
     * @var array<string, object>
     */
    private array $container = [];


    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     * @throws ReflectionException
     * @throws Exception
     */
    public function get(string $class): object
    {
        if (isset($this->container[$class])) {
            return $this->container[$class];
        }
        $instance = $this->instantiate($class);
        $this->container[$class] = $instance;
        return $instance;
    }

    /**
     * @throws ReflectionException
     * @throws Exception
     */
    private function instantiate(string $class): object
    {
        $reflectionClass = new ReflectionClass($class);

        $constructor = $reflectionClass->getConstructor();
        if ($constructor === null) {
            return new $class();
        }
        if (!$reflectionClass->isInstantiable()) {
            throw new Exception("Class is not instantiable: " . $class);
        }
        if ($constructor->isPrivate()) {
            throw new Exception("Constructor is private: " . $class);
        }
        return $reflectionClass->newInstance(
            ...$this->resolveDependencies($constructor->getParameters())
        );
    }

    /**
     * @throws Exception
     */
    private function resolveDependencies(array $parameters): array
    {
        $dependencies = [];
        foreach ($parameters as $parameter) {
            $dependencies[] = $this->resolveDependency($parameter);
        }
        return $dependencies;
    }

    /**
     * @throws Exception
     */
    private function resolveDependency(ReflectionParameter $parameter): object
    {
        if ($parameter->getType() === null) {
            throw new Exception("Parameter is not typed: " . $parameter->getName());
        }
        if ($parameter->getType()->isBuiltin()) {
            throw new Exception("Parameter is builtin: " . $parameter->getName());
        }
        return $this->get($parameter->getType()->getName());
    }
}