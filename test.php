<?php

namespace foo;

require "vendor/autoload.php";

class Registry {
}

class Application {
    public function __construct(
        private Registry $registry
    )
    {
    }
    public function doSomething(): void
    {
        echo "ok\n";
    }
}

namespace bar;

use Tivins\DI\ClassAnalyzer;
use Tivins\DI\Container;
use foo\Application;

$container = new Container(new ClassAnalyzer(__dir__ . '/.di/cache'));
$application = $container->get(Application::class);
$application->doSomething();
