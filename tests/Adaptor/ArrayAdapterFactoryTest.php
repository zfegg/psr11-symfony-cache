<?php

declare(strict_types=1);

namespace Zfegg\Psr11SymfonyCache\Test\Adaptor;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Zfegg\Psr11SymfonyCache\Adapter\ArrayAdapterFactory;
use Zfegg\Psr11SymfonyCache\CacheFactory;

/**
 * @covers \Zfegg\Psr11SymfonyCache\Adapter\ArrayAdapterFactory
 */
class ArrayAdapterFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $factory = new ArrayAdapterFactory();
        $result = $factory([]);
        $this->assertInstanceOf(ArrayAdapter::class, $result);
    }

    public function testInvokeWithOptions(): void
    {
        $defaultLifetime = 4300;
        $storeSerialized = false;
        $maxLifetime = 20;
        $maxItems = 30;

        $options = [
            'defaultLifetime' => $defaultLifetime,
            'storeSerialized' => $storeSerialized,
            'maxLifetime' => $maxLifetime,
            'maxItems' => $maxItems,
        ];

        $mockAdapter = $this->getMockBuilder(ArrayAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var MockObject|ArrayAdapterFactory $factory */
        $factory = $this->getMockBuilder(ArrayAdapterFactory::class)
            ->onlyMethods(['getAdapter'])
            ->getMock();

        $factory->expects($this->once())
            ->method('getAdapter')
            ->with(
                $this->equalTo($defaultLifetime),
                $this->equalTo($storeSerialized),
                $this->equalTo($maxLifetime),
                $this->equalTo($maxItems)
            )->willReturn($mockAdapter);

        $result = $factory->__invoke($options);

        $this->assertEquals($mockAdapter, $result);
    }
}
