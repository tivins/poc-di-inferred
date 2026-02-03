<?php

namespace Tivins\DI;

use Exception;
use ReflectionClass;
use ReflectionException;

class Container
{
    /**
     * @var array<string, object>
     */
    private array $container = [];

    public function __construct(private readonly ClassAnalyzer $analyzer)
    {
    }

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