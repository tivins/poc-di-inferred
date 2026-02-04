<?php

namespace Tivins\DI\Tests\Fixtures;

class ClassWithPrivateConstructor
{
    private function __construct()
    {
    }

    public function greet(): string
    {
        return 'private';
    }
}
