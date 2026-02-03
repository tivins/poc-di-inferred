<?php

namespace Tivins\DI;

class CacheFile implements CacheInterface
{
    private string $cacheDir;

    public function __construct(string $cacheDir)
    {
        $this->cacheDir = $cacheDir;
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    public function get(string $key): ?string
    {
        $filename = $this->cacheDir . '/' . $this->safeKey($key);
        if (!is_readable($filename)) {
            return null;
        }
        $content = file_get_contents($filename);
        return $content === false ? null : $content;
    }

    public function set(string $key, string $value): void
    {
        file_put_contents($this->cacheDir . '/' . $this->safeKey($key), $value);
    }

    private function safeKey(string $key): string
    {
        return sha1($key);
    }
}