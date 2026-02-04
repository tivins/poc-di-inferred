<?php

namespace Tivins\DI\Infrastructure;

use Tivins\DI\Core\CacheInterface;

class MemoryCache implements CacheInterface
{
    private array $memory = [];

    public function get(string $key): ?string
    {
        return $this->memory[$key] ?? null;
    }

    public function set(string $key, string $value): bool
    {
        $this->memory[$key] = $value;
        return true;
    }

    public function delete(string $key): void
    {
        unset($this->memory[$key]);
    }

    public function clear(): void
    {
        $this->memory = [];
    }
}