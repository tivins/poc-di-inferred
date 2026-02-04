<?php

namespace Tivins\DI\Tests\Fixtures;

abstract class AbstractBase
{
    public function __construct()
    {
    }

    public function greet(): string
    {
        return 'abstract';
    }
}
