<?php

namespace Tivins\DI\Infrastructure;

use RuntimeException;
use Tivins\DI\Core\CacheInterface;

class CacheRedis implements CacheInterface
{

    public function get(string $key): ?string
    {
        throw new RuntimeException('Not implemented');
    }

    public function set(string $key, string $value): bool
    {
        throw new RuntimeException('Not implemented');
    }

    public function delete(string $key): void
    {
        throw new RuntimeException('Not implemented');
    }

    public function clear(): void
    {
        throw new RuntimeException('Not implemented');
    }
}