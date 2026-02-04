<?php

namespace Tivins\DI\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tivins\DI\Infrastructure\CacheFile;

class CacheFileTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = sys_get_temp_dir() . '/di-cache-test-' . uniqid('', true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            (new CacheFile($this->tempDir))->clear();
            @rmdir($this->tempDir);
        }
        parent::tearDown();
    }

    public function testSetAndGet(): void
    {
        $cache = new CacheFile($this->tempDir);
        $cache->set('user:1', '{"name":"Alice"}');

        $this->assertSame('{"name":"Alice"}', $cache->get('user:1'));
    }

    public function testGetMissingReturnsNull(): void
    {
        $cache = new CacheFile($this->tempDir);

        $this->assertNull($cache->get('missing'));
    }

    public function testDelete(): void
    {
        $cache = new CacheFile($this->tempDir);
        $cache->set('session:abc', 'data');
        $cache->delete('session:abc');

        $this->assertNull($cache->get('session:abc'));
    }

    public function testClear(): void
    {
        $cache = new CacheFile($this->tempDir);
        $cache->set('a', '1');
        $cache->set('b', '2');
        $cache->clear();

        $this->assertNull($cache->get('a'));
        $this->assertNull($cache->get('b'));
    }

    public function testCreatesCacheDir(): void
    {
        $dir = $this->tempDir . '/nested/sub';
        new CacheFile($dir);

        $this->assertDirectoryExists($dir);
    }
}
