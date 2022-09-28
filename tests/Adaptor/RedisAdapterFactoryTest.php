<?php

declare(strict_types=1);

namespace Zfegg\Psr11SymfonyCache\Test\Adaptor;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Traits\RedisProxy;
use Zfegg\Psr11SymfonyCache\Adapter\RedisAdapterFactory;
use Zfegg\Psr11SymfonyCache\Exception\MissingConfigException;

/**
 * @covers \Zfegg\Psr11SymfonyCache\Adapter\RedisAdapterFactory
 */
class RedisAdapterFactoryTest extends TestCase
{
    public function testGetConnection(): void
    {
        $dsn = 'redis://127.0.0.1';
        $params = ['lazy' => true];

        $factory = new RedisAdapterFactory();
        $result = $factory->getConnection($dsn, $params);
        $this->assertInstanceOf(RedisProxy::class, $result);
    }

    public function testGetClient(): void
    {
        $client = null;
        $dsn = 'redis://127.0.0.1';

        $expected = [
            'class' => 'MyClass',
            'compression' => false,
            'lazy' => true,
            'persistent' => 678,
            'persistent_id' => 'some_id',
            'read_timeout' => 1654,
            'retry_interval' => 7546,
            'tcp_keepalive' => 105,
            'timeout' => 754,
        ];

        $options = $expected;
        $options['dsn'] = $dsn;

        $mockConnection = $this->getMockBuilder(RedisProxy::class)
            ->disableOriginalConstructor()
            ->getMock();

        $factory = $this->getMockBuilder(RedisAdapterFactory::class)
            ->onlyMethods(['getConnection'])
            ->getMock();

        $factory->expects($this->once())
            ->method('getConnection')
            ->with(
                $this->equalTo($dsn),
                $this->equalTo($expected)
            )->willReturn($mockConnection);

        $result = $factory->getClient($options);
        $this->assertEquals($mockConnection, $result);
    }

    public function testGetClientWithDefaults(): void
    {
        $client = null;
        $dsn = 'redis://127.0.0.1';

        $expected = [
            'class' => '\Redis',
            'compression' => true,
            'lazy' => false,
            'persistent' => 0,
            'persistent_id' => '',
            'read_timeout' => 0,
            'retry_interval' => 0,
            'tcp_keepalive' => 0,
            'timeout' => 30,
        ];

        $options = [];
        $options['dsn'] = $dsn;

        $mockConnection = $this->getMockBuilder(RedisProxy::class)
            ->disableOriginalConstructor()
            ->getMock();

        $factory = $this->getMockBuilder(RedisAdapterFactory::class)
            ->onlyMethods(['getConnection'])
            ->getMock();

        $factory->expects($this->once())
            ->method('getConnection')
            ->with(
                $this->equalTo($dsn),
                $this->equalTo($expected)
            )->willReturn($mockConnection);

        $result = $factory->getClient($options);
        $this->assertEquals($mockConnection, $result);
    }

    public function testGetClientFromTheContainer(): void
    {
        $client = 'Some-Service';
        $options = ['client' => $client];

        $mockContainer = $this->createMock(ContainerInterface::class);
        $mockConnection = $this->getMockBuilder(RedisProxy::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockContainer->expects($this->once())
            ->method('has')
            ->with($this->equalTo($client))
            ->willReturn(true);

        $mockContainer->expects($this->once())
            ->method('get')
            ->with($this->equalTo($client))
            ->willReturn($mockConnection);

        $factory = new RedisAdapterFactory();
        $factory->setContainer($mockContainer);

        $result = $factory->getClient($options);
        $this->assertEquals($mockConnection, $result);
    }

    public function testGetClientMissingClientAndDsn(): void
    {
        $this->expectException(MissingConfigException::class);
        $options = [];
        $factory = new RedisAdapterFactory();
        $factory->getClient($options);
    }

    public function testGetAdapter(): void
    {
        $mockConnection = $this->getMockBuilder(RedisProxy::class)
            ->disableOriginalConstructor()
            ->getMock();

        $factory = new RedisAdapterFactory();
        $result = $factory->getAdapter($mockConnection, '', 0);
        $this->assertInstanceOf(RedisAdapter::class, $result);
    }

    public function testInvoke(): void
    {
        $namespace = 'some_namespace';
        $lifetime = 324;

        $options = [
            'namespace' => $namespace,
            'maxLifetime' => $lifetime
        ];

        $mockConnection = $this->getMockBuilder(RedisProxy::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockAdapter = $this->getMockBuilder(RedisAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $factory = $factory = $this->getMockBuilder(RedisAdapterFactory::class)
            ->onlyMethods(['getClient', 'getAdapter'])
            ->getMock();

        $factory->expects($this->once())
            ->method('getClient')
            ->with($this->equalTo($options))
            ->willReturn($mockConnection);

        $factory->expects($this->once())
            ->method('getAdapter')
            ->with(
                $this->equalTo($mockConnection),
                $this->equalTo($namespace),
                $this->equalTo($lifetime)
            )->willReturn($mockAdapter);

        $result = $factory->__invoke($options);
        $this->assertEquals($mockAdapter, $result);
    }

    public function testInvokeWithDefaults(): void
    {
        $namespace = '';
        $lifetime = 0;

        $options = [];

        $mockConnection = $this->getMockBuilder(RedisProxy::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockAdapter = $this->getMockBuilder(RedisAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $factory = $factory = $this->getMockBuilder(RedisAdapterFactory::class)
            ->onlyMethods(['getClient', 'getAdapter'])
            ->getMock();

        $factory->expects($this->once())
            ->method('getClient')
            ->with($this->equalTo($options))
            ->willReturn($mockConnection);

        $factory->expects($this->once())
            ->method('getAdapter')
            ->with(
                $this->equalTo($mockConnection),
                $this->equalTo($namespace),
                $this->equalTo($lifetime)
            )->willReturn($mockAdapter);

        $result = $factory->__invoke($options);
        $this->assertEquals($mockAdapter, $result);
    }
}
