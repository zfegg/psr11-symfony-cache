<?php

declare(strict_types=1);

namespace Zfegg\Psr11SymfonyCache\Test\Adaptor;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\PhpArrayAdapter;
use Zfegg\Psr11SymfonyCache\Adapter\PhpArrayAdapterFactory;
use Zfegg\Psr11SymfonyCache\Exception\InvalidConfigException;
use Zfegg\Psr11SymfonyCache\Exception\MissingConfigException;

/**
 * @covers \Zfegg\Psr11SymfonyCache\Adapter\PhpArrayAdapterFactory
 */
class PhpArrayAdapterFactoryTest extends TestCase
{
    /** @var PhpArrayAdapterFactory */
    protected $factory;

    /** @var MockObject|ContainerInterface */
    protected $mockContainer;

    /** @var MockObject|AdapterInterface */
    protected $mockAdapter;

    protected function setUp(): void
    {
        $this->mockContainer = $this->createMock(ContainerInterface::class);
        $this->mockAdapter = $this->createMock(AdapterInterface::class);
        $this->factory = new PhpArrayAdapterFactory();
        $this->factory->setContainer($this->mockContainer);
        $this->assertInstanceOf(PhpArrayAdapterFactory::class, $this->factory);
    }

    public function testConstructor(): void
    {
    }

    public function testInvoke(): void
    {
        $backupCache = 'some-service-name';
        $filePath = '/my/path';

        $options = [
            'backupCache' => $backupCache,
            'filePath' => $filePath
        ];

        $this->mockContainer->expects($this->once())
            ->method('has')
            ->with($this->equalTo($backupCache))
            ->willReturn(true);

        $this->mockContainer->expects($this->once())
            ->method('get')
            ->with($this->equalTo($backupCache))
            ->willReturn($this->mockAdapter);

        $result = $this->factory->__invoke($options);
        $this->assertInstanceOf(PhpArrayAdapter::class, $result);
    }

    public function testInvokeInvalidService(): void
    {
        $this->expectException(InvalidConfigException::class);

        $backupCache = 'some-service-name';
        $filePath = '/my/path';

        $options = [
            'backupCache' => $backupCache,
            'filePath' => $filePath
        ];

        $this->mockContainer->expects($this->once())
            ->method('has')
            ->with($this->equalTo($backupCache))
            ->willReturn(false);

        $this->mockContainer->expects($this->never())
            ->method('get');

        $this->factory->__invoke($options);
    }

    public function testInvokeMissingBackupCacheInConfig(): void
    {
        $this->expectException(MissingConfigException::class);

        $filePath = '/my/path';

        $options = [
            'filePath' => $filePath
        ];

        $this->mockContainer->expects($this->never())
            ->method('has');

        $this->mockContainer->expects($this->never())
            ->method('get');

        $this->factory->__invoke($options);
    }

    public function testInvokeMissingFilepathInConfig(): void
    {
        $this->expectException(MissingConfigException::class);

        $backupCache = 'some-service-name';

        $options = [
            'backupCache' => $backupCache,
        ];

        $this->mockContainer->expects($this->never())
            ->method('has');

        $this->mockContainer->expects($this->never())
            ->method('get');

        $this->factory->__invoke($options);
    }
}
