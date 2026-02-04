<?php

namespace Tivins\DI\Infrastructure;

use Tivins\DI\Core\CacheInterface;

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
        $filename = $this->getFilename($key);
        if (!is_readable($filename)) {
            return null;
        }
        $content = file_get_contents($filename);
        return $content === false ? null : $content;
    }

    public function set(string $key, string $value): bool
    {
        $filename = $this->getFilename($key);
        if (is_writable($filename)) {
            return file_put_contents($filename, $value) !== false;
        }
        return false;
    }

    public function delete(string $key): void
    {
        $filename = $this->cacheDir . '/' . $this->safeKey($key);
        if (!is_writable($filename)) {
            return;
        }
        unlink($filename);
    }

    public function clear(): void
    {
        $files = glob($this->cacheDir . '/*');
        foreach ($files as $file) {
            if (is_file($file) && is_writable($file)) {
                unlink($file);
            }
        }
    }

    private function safeKey(string $key): string
    {
        return sha1($key);
    }
    private function getFilename(string $key): string
    {
        return $this->cacheDir . '/' . $this->safeKey($key);
    }
}