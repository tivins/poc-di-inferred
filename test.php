<?php

# ------------------------------------- namespace foo -------------------------------------

namespace foo;

require "vendor/autoload.php";

interface RegistryInterface
{
    public function get(string $key): string;
}

class Registry implements RegistryInterface
{
    public function get(string $key): string
    {
        return "[$key]";
    }
}

readonly class Application
{
    public function __construct(
        private RegistryInterface $registry,
        # and more class/interfaces
    )
    {
    }

    public function doSomething(string $key): string
    {
        return $this->registry->get($key);
    }
}

# ------------------------------------- namespace bar -------------------------------------

namespace bar;

use foo\Application;
use foo\Registry;
use foo\RegistryInterface;
use Throwable;
use Tivins\DI\CacheFile;
use Tivins\DI\CacheInterface;
use Tivins\DI\ClassAnalyzer;
use Tivins\DI\Container;

# Example 1
(function () {
    // bootstrap of DI container
    $container = new Container(new ClassAnalyzer(new CacheFile(__dir__ . '/.di/cache')));
    $container->bind(RegistryInterface::class, Registry::class);

    // Usage
    try {
        $application = $container->get(Application::class);
        var_dump($application->doSomething("Test !")); # OK (maybe with cache write)

        $application = $container->get(Application::class);
        var_dump($application->doSomething("Test !")); # OK (with cache for sure !)

        try {
            $container->remove(RegistryInterface::class);
            $container->get(Application::class); # Throws Exception because RegistryInterface is not instantiable.
        } catch (Throwable $e) {
            echo "Unbounded registry interface!\n";
        }
    } catch (Throwable $e) {
        echo "Something went wrong!\n";
        var_dump($e->getMessage());
    }
})();

# Exemple 2 : Customize
(function () {

    class MemoryCache implements CacheInterface {
        private array $memory = [];
        public function get(string $key): ?string{ return $this->memory[$key] ?? null; }
        public function set(string $key, string $value): bool { $this->memory[$key] = $value; return true; }
        public function delete(string $key): void { unset($this->memory[$key]); }
        public function clear(): void { $this->memory = []; }
    }

    $container = new Container(new ClassAnalyzer(new MemoryCache()));
    $container->bind(RegistryInterface::class, Registry::class);
    $application = $container->get(Application::class); # OK (-> cache write in memory)
    var_dump($application->doSomething("Test !"));
})();