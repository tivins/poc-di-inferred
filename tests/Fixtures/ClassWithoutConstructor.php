<?php

namespace Tivins\DI\Tests\Fixtures;

class ClassWithoutConstructor
{
    public function greet(): string
    {
        return 'Hello';
    }
}
