<?php

declare(strict_types=1);

namespace Zfegg\Psr11SymfonyCache\Test\Adaptor;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Zfegg\Psr11SymfonyCache\Adapter\FilesystemAdapterFactory;

/**
 * @covers \Zfegg\Psr11SymfonyCache\Adapter\FilesystemAdapterFactory
 */
class FilesystemAdapterFactoryTest extends TestCase
{
    /** @var FilesystemAdapterFactory */
    protected $factory;

    public function testGetAdapter(): void
    {
        $factory = new FilesystemAdapterFactory();
        $result = $factory->getAdapter('', 0, '');
        $this->assertInstanceOf(FilesystemAdapter::class, $result);
    }

    public function testInvoke(): void
    {
        $directory = 'some/dir';
        $namespace = 'some-namespace';
        $lifetime = 45;

        $options = [
            'directory' => $directory,
            'namespace' => $namespace,
            'defaultLifetime' => $lifetime
        ];

        $mockAdapter = $this->getMockBuilder(FilesystemAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var FilesystemAdapterFactory|MockObject $factory */
        $factory = $this->getMockBuilder(FilesystemAdapterFactory::class)
            ->onlyMethods(['getAdapter'])
            ->getMock();

        $factory->expects($this->once())
            ->method('getAdapter')
            ->with(
                $this->equalTo($namespace),
                $this->equalTo($lifetime),
                $this->equalTo($directory)
            )->willReturn($mockAdapter);

        $result = $factory->__invoke($options);
        $this->assertEquals($mockAdapter, $result);
    }

    public function testInvokeWithDefaults(): void
    {
        $directory = '';
        $namespace = '';
        $lifetime = 0;

        $options = [];

        $mockAdapter = $this->getMockBuilder(FilesystemAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var FilesystemAdapterFactory|MockObject $factory */
        $factory = $this->getMockBuilder(FilesystemAdapterFactory::class)
            ->onlyMethods(['getAdapter'])
            ->getMock();

        $factory->expects($this->once())
            ->method('getAdapter')
            ->with(
                $this->equalTo($namespace),
                $this->equalTo($lifetime),
                $this->equalTo($directory)
            )->willReturn($mockAdapter);

        $result = $factory->__invoke($options);
        $this->assertEquals($mockAdapter, $result);
    }
}
