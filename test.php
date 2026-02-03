<?php

use Tivins\DI\Container;

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

$container = new Container();
$application = $container->get(Application::class);
$application->doSomething();
