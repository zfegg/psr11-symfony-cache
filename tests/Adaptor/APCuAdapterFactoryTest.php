<?php

declare(strict_types=1);

namespace Zfegg\Psr11SymfonyCache\Test\Adaptor;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Zfegg\Psr11SymfonyCache\Adapter\APCuAdapterFactory;
use Zfegg\Psr11SymfonyCache\CacheFactory;

/**
 * @covers \Zfegg\Psr11SymfonyCache\Adapter\APCuAdapterFactory
 */
class APCuAdapterFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $factory = new APCuAdapterFactory();
        $result = $factory([]);
        $this->assertInstanceOf(ApcuAdapter::class, $result);
    }

    public function testInvokeWithVersion(): void
    {
        $factory = new APCuAdapterFactory();
        $result = $factory(['version' => 'testing']);
        $this->assertInstanceOf(ApcuAdapter::class, $result);
    }

    public function testInvokeWithOptions(): void
    {
        $namespace = 'some-namespace';
        $lifetime = 4300;
        $version = 3;

        $options = [
            'namespace' => $namespace,
            'defaultLifetime' => $lifetime,
            'version' => $version
        ];

        $mockAdapter = $this->getMockBuilder(ApcuAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var MockObject|APCuAdapterFactory $factory */
        $factory = $this->getMockBuilder(APCuAdapterFactory::class)
            ->onlyMethods(['getAdapter'])
            ->getMock();

        $factory->expects($this->once())
            ->method('getAdapter')
            ->with(
                $this->equalTo($namespace),
                $this->equalTo($lifetime),
                $this->equalTo($version)
            )->willReturn($mockAdapter);

        $result = $factory->__invoke($options);

        $this->assertEquals($mockAdapter, $result);


        $mockContainer = $this->createMock(ContainerInterface::class);
        $mockContainer->expects($this->once())
            ->method('has')
            ->with($this->equalTo('config'))
            ->willReturn(true);

        $mockContainer->expects($this->once())
            ->method('get')
            ->with($this->equalTo('config'))
            ->willReturn([
                'cache' => [
                    'default' => [
                        'type' => 'apcu',
                        'options' => $options
                    ]
                ]
            ]);

        $factory = new CacheFactory();
        $result = $factory($mockContainer);
        $this->assertEquals($mockAdapter, $result);
    }
}
