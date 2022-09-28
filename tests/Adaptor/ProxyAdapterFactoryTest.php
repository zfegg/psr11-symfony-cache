<?php

declare(strict_types=1);

namespace Zfegg\Psr11SymfonyCache\Test\Adaptor;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\ProxyAdapter;
use Zfegg\Psr11SymfonyCache\Adapter\ProxyAdapterFactory;
use Zfegg\Psr11SymfonyCache\Exception\InvalidConfigException;
use Zfegg\Psr11SymfonyCache\Exception\MissingConfigException;

/**
 * @covers \Zfegg\Psr11SymfonyCache\Adapter\ProxyAdapterFactory
 */
class ProxyAdapterFactoryTest extends TestCase
{
    /** @var MockObject|ContainerInterface */
    protected $mockContainer;

    /** @var MockObject|CacheItemPoolInterface */
    protected $mockCache;

    /** @var MockObject|ProxyAdapter */
    protected $mockAdapter;

    protected function setUp(): void
    {
        $this->mockContainer = $this->createMock(ContainerInterface::class);
        $this->mockCache = $this->createMock(CacheItemPoolInterface::class);
        $this->mockAdapter = $this->createMock(ProxyAdapter::class);
    }

    public function testGetAdapter(): void
    {
        $factory = new ProxyAdapterFactory();
        $result = $factory->getAdapter($this->mockCache, '', 0);
        $this->assertInstanceOf(ProxyAdapter::class, $result);
    }

    public function testInvoke(): void
    {
        $serviceName = 'some-service';
        $namespace = 'some-namespace';
        $lifetime = 4321;

        $options = [
            'psr6Service' => $serviceName,
            'namespace' => $namespace,
            'defaultLifetime' => $lifetime
        ];

        $factory = $this->getMockBuilder(ProxyAdapterFactory::class)
            ->onlyMethods(['getAdapter'])
            ->getMock();

        $factory->setContainer($this->mockContainer);

        $this->mockContainer->expects($this->once())
            ->method('has')
            ->with($serviceName)
            ->willReturn(true);

        $this->mockContainer->expects($this->once())
            ->method('get')
            ->with($serviceName)
            ->willReturn($this->mockCache);

        $factory->expects($this->once())
            ->method('getAdapter')
            ->with(
                $this->equalTo($this->mockCache),
                $this->equalTo($namespace),
                $this->equalTo($lifetime)
            )->willReturn($this->mockAdapter);

        $result = $factory->__invoke($options);
        $this->assertEquals($this->mockAdapter, $result);
    }

    public function testInvokeWithDefaults(): void
    {
        $serviceName = 'some-service';
        $namespace = '';
        $lifetime = 0;

        $options = [
            'psr6Service' => $serviceName
        ];

        $factory = $this->getMockBuilder(ProxyAdapterFactory::class)
            ->onlyMethods(['getAdapter'])
            ->getMock();

        $factory->setContainer($this->mockContainer);

        $this->mockContainer->expects($this->once())
            ->method('has')
            ->with($serviceName)
            ->willReturn(true);

        $this->mockContainer->expects($this->once())
            ->method('get')
            ->with($serviceName)
            ->willReturn($this->mockCache);

        $factory->expects($this->once())
            ->method('getAdapter')
            ->with(
                $this->equalTo($this->mockCache),
                $this->equalTo($namespace),
                $this->equalTo($lifetime)
            )->willReturn($this->mockAdapter);

        $result = $factory->__invoke($options);
        $this->assertEquals($this->mockAdapter, $result);
    }

    public function testInvokeWithMissingService(): void
    {
        $this->expectException(InvalidConfigException::class);

        $serviceName = 'some-service';

        $options = [
            'psr6Service' => $serviceName
        ];

        $factory = new ProxyAdapterFactory();
        $factory->setContainer($this->mockContainer);

        $this->mockContainer->expects($this->once())
            ->method('has')
            ->with($serviceName)
            ->willReturn(false);

        $this->mockContainer->expects($this->never())
            ->method('get');

        $factory->__invoke($options);
    }

    public function testInvokeWithMissingServiceInConfig(): void
    {
        $this->expectException(MissingConfigException::class);

        $options = [];

        $factory = new ProxyAdapterFactory();
        $factory->setContainer($this->mockContainer);

        $this->mockContainer->expects($this->never())
            ->method('has');

        $this->mockContainer->expects($this->never())
            ->method('get');

        $factory->__invoke($options);
    }
}
