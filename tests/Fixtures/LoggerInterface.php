<?php

namespace Tivins\DI\Tests\Fixtures;

interface LoggerInterface
{
    public function log(string $message): void;
}
