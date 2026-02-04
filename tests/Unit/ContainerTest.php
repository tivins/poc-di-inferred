<?php

namespace Tivins\DI\Tests\Unit;

use Exception;
use PHPUnit\Framework\TestCase;
use Tivins\DI\Core\ClassAnalyzer;
use Tivins\DI\Core\Container;
use Tivins\DI\Infrastructure\CacheMemory;
use Tivins\DI\Tests\Fixtures\AbstractBase;
use Tivins\DI\Tests\Fixtures\ClassWithoutConstructor;
use Tivins\DI\Tests\Fixtures\ClassWithPrivateConstructor;
use Tivins\DI\Tests\Fixtures\ClassWithUntypedParam;
use Tivins\DI\Tests\Fixtures\Config;
use Tivins\DI\Tests\Fixtures\Logger;
use Tivins\DI\Tests\Fixtures\LoggerInterface;
use Tivins\DI\Tests\Fixtures\OrderService;

class ContainerTest extends TestCase
{
    private function createContainer(): Container
    {
        return new Container(new ClassAnalyzer(new CacheMemory()));
    }

    public function testGetClassWithoutConstructor(): void
    {
        $container = $this->createContainer();
        $instance = $container->get(ClassWithoutConstructor::class);

        $this->assertInstanceOf(ClassWithoutConstructor::class, $instance);
        $this->assertSame('Hello', $instance->greet());
    }

    public function testBindAndResolve(): void
    {
        $container = $this->createContainer();
        $container->bind(LoggerInterface::class, Logger::class);

        $logger = $container->get(LoggerInterface::class);
        $this->assertInstanceOf(Logger::class, $logger);
    }

    public function testSingleton(): void
    {
        $container = $this->createContainer();
        $container->bind(LoggerInterface::class, Logger::class);

        $a = $container->get(OrderService::class);
        $b = $container->get(OrderService::class);

        $this->assertSame($a, $b);
        $this->assertSame($a->placeOrder('ORD-1'), 'order:ORD-1');
    }

    public function testRealLifeOrderService(): void
    {
        $container = $this->createContainer();
        $container->bind(LoggerInterface::class, Logger::class);

        /** @var OrderService $service */
        $service = $container->get(OrderService::class);
        $service->placeOrder('ORD-42');

        $logger = $container->get(LoggerInterface::class);
        $this->assertInstanceOf(Logger::class, $logger);
        $lines = $logger->getLines();
        $this->assertNotEmpty(array_filter($lines, fn(string $line): bool => str_contains($line, 'Order placed: ORD-42')));
    }

    public function testGetDump(): void
    {
        $container = $this->createContainer();
        $container->bind(LoggerInterface::class, Logger::class);
        $container->get(OrderService::class);

        $dump = $container->getDump();

        $this->assertArrayHasKey('container', $dump);
        $this->assertArrayHasKey('bindings', $dump);
        $this->assertArrayHasKey(LoggerInterface::class, $dump['bindings']);
        $this->assertSame(Logger::class, $dump['bindings'][LoggerInterface::class]);
    }

    public function testRemovePurgesAndUnboundInterfaceThrows(): void
    {
        $container = $this->createContainer();
        $container->bind(LoggerInterface::class, Logger::class);
        $container->get(OrderService::class);

        $container->remove(LoggerInterface::class);

        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Cannot instantiate interface');
        $container->get(OrderService::class);
    }

    public function testNotInstantiableThrows(): void
    {
        $container = $this->createContainer();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Class is not instantiable: ' . AbstractBase::class);
        $container->get(AbstractBase::class);
    }

    public function testConstructorPrivateThrows(): void
    {
        // ClassAnalyzer returns isInstantiable=false for private constructor in PHP,
        // so we use a custom analyzer that returns constructorPrivate=true to cover that branch.
        $analyzer = new class(new CacheMemory()) extends ClassAnalyzer {
            public function getConstructorAnalysis(string $class): array
            {
                return [
                    'hasConstructor' => true,
                    'isInstantiable' => true,
                    'constructorPrivate' => true,
                    'parameters' => [],
                ];
            }
        };
        $container = new Container($analyzer);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Constructor is private: ' . ClassWithPrivateConstructor::class);
        $container->get(ClassWithPrivateConstructor::class);
    }

    public function testUntypedParameterThrows(): void
    {
        $container = $this->createContainer();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('not typed');
        $container->get(ClassWithUntypedParam::class);
    }

    public function testResolveConcreteWithDependencies(): void
    {
        $container = $this->createContainer();
        $container->bind(LoggerInterface::class, Logger::class);

        $service = $container->get(OrderService::class);
        $this->assertInstanceOf(OrderService::class, $service);
        $result = $service->placeOrder('X');
        $this->assertSame('order:X', $result);
    }
}
