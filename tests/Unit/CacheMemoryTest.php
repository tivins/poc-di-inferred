<?php

namespace Tivins\DI\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tivins\DI\Infrastructure\CacheMemory;

class CacheMemoryTest extends TestCase
{
    public function testSetAndGet(): void
    {
        $cache = new CacheMemory();
        $cache->set('foo', 'bar');

        $this->assertSame('bar', $cache->get('foo'));
    }

    public function testGetMissingReturnsNull(): void
    {
        $cache = new CacheMemory();

        $this->assertNull($cache->get('missing'));
    }

    public function testDelete(): void
    {
        $cache = new CacheMemory();
        $cache->set('key', 'value');
        $cache->delete('key');

        $this->assertNull($cache->get('key'));
    }

    public function testClear(): void
    {
        $cache = new CacheMemory();
        $cache->set('a', '1');
        $cache->set('b', '2');
        $cache->clear();

        $this->assertNull($cache->get('a'));
        $this->assertNull($cache->get('b'));
    }

    public function testSetReturnsTrue(): void
    {
        $cache = new CacheMemory();
        $this->assertTrue($cache->set('k', 'v'));
    }
}
