<?php

declare(strict_types=1);

namespace Zfegg\Psr11SymfonyCache\Test\Adaptor;

use Doctrine\Common\Cache\CacheProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\DoctrineAdapter;
use Zfegg\Psr11SymfonyCache\Adapter\DoctrineAdapterFactory;
use Zfegg\Psr11SymfonyCache\Exception\MissingConfigException;

/**
 * @covers \Zfegg\Psr11SymfonyCache\Adapter\DoctrineAdapterFactory
 */
class DoctrineAdapterFactoryTest extends TestCase
{
    /** @var DoctrineAdapterFactory */
    protected $factory;

    /** @var MockObject|ContainerInterface */
    protected $mockContainer;

    /** @var MockObject|CacheProvider */
    protected $mockCache;

    protected function setUp(): void
    {
        $this->mockContainer = $this->createMock(ContainerInterface::class);
        $this->mockCache = $this->createMock(CacheProvider::class);
        $this->factory = new DoctrineAdapterFactory();
        $this->factory->setContainer($this->mockContainer);
        $this->assertInstanceOf(DoctrineAdapterFactory::class, $this->factory);
    }

    public function testConstructor(): void
    {
    }

    public function testGetProvider(): void
    {
        $service = 'some-service';
        $this->mockContainer->expects($this->once())
            ->method('get')
            ->with($this->equalTo($service))
            ->willReturn($this->mockCache);

        $result = $this->factory->getProvider($service);
        $this->assertEquals($this->mockCache, $result);
    }

    public function testGetAdapter(): void
    {
        $namespace = 'some-namespace';
        $lifetime = 334;

        $result = $this->factory->getAdapter($this->mockCache, $namespace, $lifetime);
        $this->assertInstanceOf(DoctrineAdapter::class, $result);
    }

    public function testInvoke(): void
    {
        $provider = 'some-service';
        $namespace = 'some-namespace';
        $lifetime = 334;

        $options = [
            'provider' => $provider,
            'namespace' => $namespace,
            'maxLifetime' => $lifetime
        ];

        $mockAdapter = $this->getMockBuilder(DoctrineAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $factory = $this->getMockBuilder(DoctrineAdapterFactory::class)
            ->onlyMethods(['getProvider', 'getAdapter'])
            ->getMock();

        $factory->expects($this->once())
            ->method('getProvider')
            ->with(
                $this->equalTo($provider)
            )->willReturn($this->mockCache);

        $factory->expects($this->once())
            ->method('getAdapter')
            ->with(
                $this->equalTo($this->mockCache),
                $this->equalTo($namespace),
                $this->equalTo($lifetime)
            )->willReturn($mockAdapter);

        $result = $factory->__invoke($options);
        $this->assertEquals($mockAdapter, $result);
    }

    public function testInvokeWithDefaults(): void
    {
        $provider = 'some-service';
        $namespace = '';
        $lifetime = 0;

        $options = [
            'provider' => $provider
        ];

        $mockAdapter = $this->getMockBuilder(DoctrineAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $factory = $this->getMockBuilder(DoctrineAdapterFactory::class)
            ->onlyMethods(['getProvider', 'getAdapter'])
            ->getMock();

        $factory->expects($this->once())
            ->method('getProvider')
            ->with(
                $this->equalTo($provider)
            )->willReturn($this->mockCache);

        $factory->expects($this->once())
            ->method('getAdapter')
            ->with(
                $this->equalTo($this->mockCache),
                $this->equalTo($namespace),
                $this->equalTo($lifetime)
            )->willReturn($mockAdapter);

        $result = $factory->__invoke($options);
        $this->assertEquals($mockAdapter, $result);
    }

    public function testInvokeMissingProvider(): void
    {
        $this->expectException(MissingConfigException::class);
        $this->factory->__invoke([]);
    }
}
