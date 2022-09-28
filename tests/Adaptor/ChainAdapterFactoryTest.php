<?php

declare(strict_types=1);

namespace Zfegg\Psr11SymfonyCache\Test\Adaptor;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\ChainAdapter;
use Zfegg\Psr11SymfonyCache\Adapter\ChainAdapterFactory;
use Zfegg\Psr11SymfonyCache\Exception\InvalidConfigException;

/**
 * @covers \Zfegg\Psr11SymfonyCache\Adapter\ChainAdapterFactory
 */
class ChainAdapterFactoryTest extends TestCase
{
    /** @var ChainAdapterFactory */
    protected $factory;

    /** @var MockObject|ContainerInterface */
    protected $mockContainer;

    /** @var MockObject|AdapterInterface */
    protected $mockCache;

    protected function setUp(): void
    {
        $this->mockContainer = $this->createMock(ContainerInterface::class);
        $this->mockCache = $this->createMock(AdapterInterface::class);
        $this->factory = new ChainAdapterFactory();
        $this->factory->setContainer($this->mockContainer);
        $this->assertInstanceOf(ChainAdapterFactory::class, $this->factory);
    }

    public function testConstructor(): void
    {
    }

    public function testGetAdapter(): void
    {
        $adapter = 'some-adapter';

        $this->mockContainer->expects($this->once())
            ->method('get')
            ->with(
                $this->equalTo($adapter)
            )->willReturn($this->mockCache);

        $result = $this->factory->getAdapter($adapter);

        $this->assertEquals($this->mockCache, $result);
    }

    public function testGetAdapters(): void
    {
        $adapter = 'some-adapter';

        $this->mockContainer->expects($this->once())
            ->method('get')
            ->with(
                $this->equalTo($adapter)
            )->willReturn($this->mockCache);

        $result = $this->factory->getAdapters([$adapter]);

        $this->assertIsArray($result);
        $this->assertEquals($this->mockCache, $result[0]);
    }

    public function testGetChain(): void
    {
        $result = $this->factory->getChain([$this->mockCache], 22);
        $this->assertInstanceOf(ChainAdapter::class, $result);
    }

    public function testInvoke(): void
    {
        $adapter = 'some-adapter';
        $maxLifetime = 22;

        $options = [
            'adapters' => [$adapter],
            'maxLifetime' => $maxLifetime
        ];

        /** @var MockObject|ChainAdapterFactory $factory */
        $factory = $this->getMockBuilder(ChainAdapterFactory::class)
            ->onlyMethods(['getChain', 'getAdapters'])
            ->getMock();

        $factory->expects($this->once())
            ->method('getAdapters')
            ->with(
                $this->equalTo([$adapter])
            )->willReturn([$this->mockCache]);

        $mockChain = $this->getMockBuilder(ChainAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $factory->expects($this->once())
            ->method('getChain')
            ->with(
                $this->equalTo([$this->mockCache]),
                $this->equalTo($maxLifetime)
            )->willReturn($mockChain);

        $result = $factory->__invoke($options);

        $this->assertEquals($mockChain, $result);
    }

    public function testInvokeMissingAdapters(): void
    {
        $this->expectException(InvalidConfigException::class);

        $maxLifetime = 22;

        $options = [
            'adapters' => [],
            'maxLifetime' => $maxLifetime
        ];

        /** @var MockObject|ChainAdapterFactory $factory */
        $factory = $this->getMockBuilder(ChainAdapterFactory::class)
            ->onlyMethods(['getChain', 'getAdapters'])
            ->getMock();

        $factory->expects($this->once())
            ->method('getAdapters')
            ->with(
                $this->equalTo([])
            )->willReturn([]);

        $factory->expects($this->never())
            ->method('getChain');

        $factory->__invoke($options);
    }

    public function testInvokeMissingAdaptersArray(): void
    {
        $this->expectException(InvalidConfigException::class);
        $this->factory->__invoke(['adapters' => 'oops']);
    }
}
