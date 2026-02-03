<?php

namespace Tivins\DI;

interface CacheInterface
{
    public function get(string $key): ?string;

    public function set(string $key, string $value): void;
}