<?php

namespace Tivins\DI\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Tivins\DI\Core\ClassAnalyzer;
use Tivins\DI\Infrastructure\CacheMemory;
use Tivins\DI\Tests\Fixtures\ClassWithoutConstructor;
use Tivins\DI\Tests\Fixtures\ClassWithPrivateConstructor;
use Tivins\DI\Tests\Fixtures\ClassWithUntypedParam;
use Tivins\DI\Tests\Fixtures\Logger;
use Tivins\DI\Tests\Fixtures\LoggerInterface;
use Tivins\DI\Tests\Fixtures\OrderService;

class ClassAnalyzerTest extends TestCase
{
    private function createAnalyzer(): ClassAnalyzer
    {
        return new ClassAnalyzer(new CacheMemory());
    }

    public function testClassWithoutConstructor(): void
    {
        $analyzer = $this->createAnalyzer();
        $analysis = $analyzer->getConstructorAnalysis(ClassWithoutConstructor::class);

        $this->assertFalse($analysis['hasConstructor']);
        $this->assertTrue($analysis['isInstantiable']);
        $this->assertFalse($analysis['constructorPrivate']);
        $this->assertSame([], $analysis['parameters']);
    }

    public function testClassWithDependencies(): void
    {
        $analyzer = $this->createAnalyzer();
        $analysis = $analyzer->getConstructorAnalysis(OrderService::class);

        $this->assertTrue($analysis['hasConstructor']);
        $this->assertTrue($analysis['isInstantiable']);
        $this->assertCount(2, $analysis['parameters']);
        $this->assertSame('logger', $analysis['parameters'][0]['name']);
        $this->assertSame(LoggerInterface::class, $analysis['parameters'][0]['type']);
        $this->assertSame('config', $analysis['parameters'][1]['name']);
        $this->assertSame(\Tivins\DI\Tests\Fixtures\Config::class, $analysis['parameters'][1]['type']);
    }

    public function testInterfaceNotInstantiable(): void
    {
        $analyzer = $this->createAnalyzer();
        $analysis = $analyzer->getConstructorAnalysis(LoggerInterface::class);

        $this->assertFalse($analysis['isInstantiable']);
    }

    public function testClassWithPrivateConstructor(): void
    {
        $analyzer = $this->createAnalyzer();
        $analysis = $analyzer->getConstructorAnalysis(ClassWithPrivateConstructor::class);

        $this->assertTrue($analysis['hasConstructor']);
        $this->assertFalse($analysis['isInstantiable']);
        $this->assertTrue($analysis['constructorPrivate']);
        $this->assertSame([], $analysis['parameters']);
    }

    public function testResultCachedWithMemoryCache(): void
    {
        $cache = new CacheMemory();
        $analyzer = new ClassAnalyzer($cache);

        $analyzer->getConstructorAnalysis(Logger::class);
        $analyzer->getConstructorAnalysis(Logger::class);

        $this->assertNotNull($cache->get(Logger::class));
    }

    public function testPurgeCache(): void
    {
        $cache = new CacheMemory();
        $analyzer = new ClassAnalyzer($cache);

        $analyzer->getConstructorAnalysis(Logger::class);
        $analyzer->purgeCache();

        $this->assertNull($cache->get(Logger::class));
    }

    public function testClassWithUntypedParam(): void
    {
        $analyzer = $this->createAnalyzer();
        $analysis = $analyzer->getConstructorAnalysis(ClassWithUntypedParam::class);

        $this->assertTrue($analysis['hasConstructor']);
        $this->assertCount(1, $analysis['parameters']);
        $this->assertSame('', $analysis['parameters'][0]['type']);
    }
}
