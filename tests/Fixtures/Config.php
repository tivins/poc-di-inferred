<?php

namespace Tivins\DI\Tests\Fixtures;

class Config
{
    public function __construct()
    {
    }

    public function getEnv(): string
    {
        return 'test';
    }
}
