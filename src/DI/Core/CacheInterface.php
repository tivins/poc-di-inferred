<?php

namespace Tivins\DI\Core;

interface CacheInterface
{
    public function get(string $key): ?string;

    public function set(string $key, string $value): bool;
    
    public function delete(string $key): void;

    public function clear(): void;
}