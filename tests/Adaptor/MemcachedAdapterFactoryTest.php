<?php

declare(strict_types=1);

namespace Zfegg\Psr11SymfonyCache\Test\Adaptor;

use Memcached;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Zfegg\Psr11SymfonyCache\Adapter\MemcachedAdapterFactory;
use Zfegg\Psr11SymfonyCache\Exception\MissingConfigException;

/**
 * @covers \Zfegg\Psr11SymfonyCache\Adapter\MemcachedAdapterFactory
 */
class MemcachedAdapterFactoryTest extends TestCase
{
    public function testGetConnection(): void
    {
        $factory = new MemcachedAdapterFactory();
        $result = $factory->getConnection(
            'memcached://localhost',
            []
        );

        $this->assertInstanceOf(Memcached::class, $result);
    }

    public function testGetClient(): void
    {
        $client = null;
        $dsn = 'some-dsn';

        $expected = [
            'auto_eject_hosts' => true,
            'buffer_writes' => true,
            'compression' => false,
            'compression_type' => 'some-compression',
            'connect_timeout' => 21354,
            'distribution' => 'distro',
            'hash' => 'myHash',
            'libketama_compatible' => false,
            'no_block' => false,
            'number_of_replicas' => 33,
            'prefix_key' => 'I am a key',
            'poll_timeout' => 4561,
            'randomize_replica_read' => true,
            'recv_timeout' => 418,
            'retry_timeout' => 987,
            'send_timeout' => 154,
            'serializer' => 'json',
            'server_failure_limit' => 684,
            'socket_recv_size' => 971,
            'tcp_keepalive' => true,
            'tcp_nodelay' => true,
            'use_udp' => true,
            'verify_key' => true,
        ];

        $options = $expected;
        $options['dsn'] = $dsn;
        $options['client'] = $client;

        $mockConnection = $this->getMockBuilder(Memcached::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var MockObject|MemcachedAdapterFactory $factory */
        $factory = $this->getMockBuilder(MemcachedAdapterFactory::class)
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
        $dsn = 'some-dsn';

        $expected = [
            'auto_eject_hosts' => false,
            'buffer_writes' => false,
            'compression' => true,
            'compression_type' => '',
            'connect_timeout' => 1000,
            'distribution' => 'consistent',
            'hash' => 'md5',
            'libketama_compatible' => true,
            'no_block' => true,
            'number_of_replicas' => 0,
            'prefix_key' => '',
            'poll_timeout' => 1000,
            'randomize_replica_read' => false,
            'recv_timeout' => 0,
            'retry_timeout' => 0,
            'send_timeout' => 0,
            'serializer' => 'php',
            'server_failure_limit' => 0,
            'socket_recv_size' => 0,
            'tcp_keepalive' => false,
            'tcp_nodelay' => false,
            'use_udp' => false,
            'verify_key' => false,
        ];

        $options = [];
        $options['dsn'] = $dsn;
        $options['client'] = $client;

        $mockConnection = $this->getMockBuilder(Memcached::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var MockObject|MemcachedAdapterFactory $factory */
        $factory = $this->getMockBuilder(MemcachedAdapterFactory::class)
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

    public function testGetClientMissingDsnAndClient(): void
    {
        $this->expectException(MissingConfigException::class);

        $factory = new MemcachedAdapterFactory();
        $factory->getClient([]);
    }

    public function testGetClientFromService(): void
    {
        $client = 'some-service';

        $options = ['client' => $client];

        $mockContainer = $this->createMock(ContainerInterface::class);
        $mockConnection = $this->getMockBuilder(Memcached::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockContainer->expects($this->once())
            ->method('get')
            ->with($this->equalTo($client))
            ->willReturn($mockConnection);

        $factory = new MemcachedAdapterFactory();
        $factory->setContainer($mockContainer);

        $result = $factory->getClient($options);
        $this->assertEquals($mockConnection, $result);
    }

    public function testGetAdapter(): void
    {
        $mockConnection = $this->getMockBuilder(Memcached::class)
            ->disableOriginalConstructor()
            ->getMock();

        $factory = new MemcachedAdapterFactory();
        $result = $factory->getAdapter($mockConnection, '', 0);
        $this->assertInstanceOf(MemcachedAdapter::class, $result);
    }

    public function testInvoke(): void
    {
        $namespace = 'some-namespace';
        $lifetime = 33424;

        $options = [
            'namespace' => $namespace,
            'maxLifetime' => $lifetime
        ];

        $mockClient = $this->getMockBuilder(Memcached::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockAdapter = $this->getMockBuilder(MemcachedAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var MockObject|MemcachedAdapterFactory $factory */
        $factory = $this->getMockBuilder(MemcachedAdapterFactory::class)
            ->onlyMethods(['getClient', 'getAdapter'])
            ->getMock();

        $factory->expects($this->once())
            ->method('getClient')
            ->with($this->equalTo($options))
            ->willReturn($mockClient);

        $factory->expects($this->once())
            ->method('getAdapter')
            ->with(
                $this->equalTo($mockClient),
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

        $mockClient = $this->getMockBuilder(Memcached::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockAdapter = $this->getMockBuilder(MemcachedAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var MockObject|MemcachedAdapterFactory $factory */
        $factory = $this->getMockBuilder(MemcachedAdapterFactory::class)
            ->onlyMethods(['getClient', 'getAdapter'])
            ->getMock();

        $factory->expects($this->once())
            ->method('getClient')
            ->with($this->equalTo($options))
            ->willReturn($mockClient);

        $factory->expects($this->once())
            ->method('getAdapter')
            ->with(
                $this->equalTo($mockClient),
                $this->equalTo($namespace),
                $this->equalTo($lifetime)
            )->willReturn($mockAdapter);

        $result = $factory->__invoke($options);
        $this->assertEquals($mockAdapter, $result);
    }
}
