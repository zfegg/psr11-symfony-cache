<?php

declare(strict_types=1);

namespace Zfegg\Psr11SymfonyCache\Test\Adaptor;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Zfegg\Psr11SymfonyCache\Adapter\PhpFilesAdapterFactory;

/**
 * @covers \Zfegg\Psr11SymfonyCache\Adapter\PhpFilesAdapterFactory
 */
class PhpFilesAdapterFactoryTest extends TestCase
{
    public function testGetAdapter(): void
    {
        $factory = new PhpFilesAdapterFactory();
        $result = $factory->getAdapter('', 0, '');
        $this->assertInstanceOf(PhpFilesAdapter::class, $result);
    }

    public function testInvoke(): void
    {
        $namespace = 'some-namespace';
        $lifetime = 342;
        $dir = '/some/path';

        $options = [
            'namespace' => $namespace,
            'defaultLifetime' => $lifetime,
            'directory' => $dir
        ];

        $mockAdapter = $this->getMockBuilder(PhpFilesAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $factory = $this->getMockBuilder(PhpFilesAdapterFactory::class)
            ->onlyMethods(['getAdapter'])
            ->getMock();

        $factory->expects($this->once())
            ->method('getAdapter')
            ->with(
                $this->equalTo($namespace),
                $this->equalTo($lifetime),
                $this->equalTo($dir)
            )->willReturn($mockAdapter);

        $result = $factory->__invoke($options);
        $this->assertEquals($mockAdapter, $result);
    }

    public function testInvokeWithDefaults(): void
    {
        $namespace = '';
        $lifetime = 0;
        $dir = '';

        $options = [];

        $mockAdapter = $this->getMockBuilder(PhpFilesAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $factory = $this->getMockBuilder(PhpFilesAdapterFactory::class)
            ->onlyMethods(['getAdapter'])
            ->getMock();

        $factory->expects($this->once())
            ->method('getAdapter')
            ->with(
                $this->equalTo($namespace),
                $this->equalTo($lifetime),
                $this->equalTo($dir)
            )->willReturn($mockAdapter);

        $result = $factory->__invoke($options);
        $this->assertEquals($mockAdapter, $result);
    }
}
