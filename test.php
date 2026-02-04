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

use Throwable;
use foo\Application;
use foo\Registry;
use foo\RegistryInterface;
use Tivins\DI\Core\ClassAnalyzer;
use Tivins\DI\Core\Container;
use Tivins\DI\Infrastructure\CacheFile;
use Tivins\DI\Infrastructure\MemoryCache;

# Example 1
(function () {
    // bootstrap of DI container
    $container = new Container(new ClassAnalyzer(new CacheFile(__dir__ . '/.di/cache')));
    $container->bind(RegistryInterface::class, Registry::class);

    // Usage
    $application = $container->get(Application::class);
    var_dump($application->doSomething("Test !")); # OK (maybe with cache write)
    echo json_encode($container->getDump()) . PHP_EOL;

    $application = $container->get(Application::class);
    var_dump($application->doSomething("Test !")); # OK (with cache for sure !)
    echo json_encode($container->getDump()) . PHP_EOL;

    try {
        # try to remove RegistryInterface from the registry
        $container->remove(RegistryInterface::class);
        $container->get(Application::class); # Throws Exception because RegistryInterface is not instantiable (no more binding).
    } catch (Throwable) {
        echo "Unbounded registry interface!\n";
    }
})();

# Exemple 2 : Customize
(function () {



    $container = new Container(new ClassAnalyzer(new MemoryCache()));
    $container->bind(RegistryInterface::class, Registry::class);
    $application = $container->get(Application::class); # OK (-> cache write in memory)
    var_dump($application->doSomething("Test from Memory"));
    $application = $container->get(Application::class); # OK (-> cache from memory)
    var_dump($application->doSomething("Test from Memory"));
})();