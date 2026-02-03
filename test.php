<?php

# ------------------------------------- namespace foo -------------------------------------

namespace foo;

require "vendor/autoload.php";

interface RegistryInterface {
}

class Registry implements RegistryInterface {
}

readonly class Application {
    public function __construct(
        private RegistryInterface $registry
    )
    {
    }
    public function doSomething(): bool
    {
        return $this->registry instanceof RegistryInterface;
    }
}

# ------------------------------------- namespace bar -------------------------------------

namespace bar;

use foo\Registry;
use foo\RegistryInterface;
use Throwable;
use Tivins\DI\ClassAnalyzer;
use Tivins\DI\Container;
use foo\Application;

// bootstrap of DI container
$container = new Container(new ClassAnalyzer(__dir__ . '/.di/cache'));
$container->bind(RegistryInterface::class, Registry::class);

// Usage
try {
    $application = $container->get(Application::class);
    var_dump($application->doSomething());
} catch (Throwable $e) {
    echo "Something went wrong!\n";
    var_dump($e->getMessage());
}
