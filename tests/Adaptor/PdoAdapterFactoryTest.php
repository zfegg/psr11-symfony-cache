<?php

declare(strict_types=1);

namespace Zfegg\Psr11SymfonyCache\Test\Adaptor;

use PDO;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\PdoAdapter;
use Zfegg\Psr11SymfonyCache\Adapter\PdoAdapterFactory;
use Zfegg\Psr11SymfonyCache\Exception\InvalidConfigException;

/**
 * @covers \Zfegg\Psr11SymfonyCache\Adapter\PdoAdapterFactory
 */
class PdoAdapterFactoryTest extends TestCase
{
    public function testGetAdapter(): void
    {
        $mockClient = $this->createMock(PDO::class);
        $namespace = '';
        $lifetime = 0;
        $params = [];

        $mockClient->expects($this->once())
            ->method('getAttribute')
            ->willReturn(PDO::ERRMODE_EXCEPTION);

        $factory = new PdoAdapterFactory();

        $result = $factory->getAdapter($mockClient, $namespace, $lifetime, $params);
        $this->assertInstanceOf(PdoAdapter::class, $result);
    }

    public function testInvoke(): void
    {
        $client = 'some-client';
        $namespace = 'my-namespace';
        $lifetime = 4342;

        $expected = [
            'db_table' => 'my-table',
            'db_id_col' => 'my-id-col',
            'db_data_col' => 'my_data_col',
            'db_lifetime_col' => 'my_lifetime_col',
            'db_time_col' => 'my_time_col',
            'db_username' => 'my_username',
            'db_password' => 'my_password',
            'db_connection_options' => ['my-db-key' => 'my-db-value'],
        ];

        $options = $expected;
        $options['client'] = $client;
        $options['namespace'] = $namespace;
        $options['maxLifetime'] = $lifetime;

        $mockContainer = $this->createMock(ContainerInterface::class);

        $mockContainer->expects($this->once())
            ->method('has')
            ->willReturn(false);

        $mockContainer->expects($this->never())
            ->method('get');

        $mockAdapter = $this->getMockBuilder(PdoAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $factory = $this->getMockBuilder(PdoAdapterFactory::class)
            ->onlyMethods(['getAdapter'])
            ->getMock();

        $factory->setContainer($mockContainer);

        $factory->expects($this->once())
            ->method('getAdapter')
            ->with(
                $this->equalTo($client),
                $this->equalTo($namespace),
                $this->equalTo($lifetime),
                $this->equalTo($expected)
            )->willReturn($mockAdapter);

        $result = $factory->__invoke($options);
        $this->assertEquals($mockAdapter, $result);
    }

    public function testInvokeWithDefaults(): void
    {
        $client = $this->createMock(PDO::class);
        $serviceName = 'some-service';
        $namespace = '';
        $lifetime = 0;

        $expected = [
            'db_table' => 'cache_items',
            'db_id_col' => 'item_id',
            'db_data_col' => 'item_id',
            'db_lifetime_col' => 'item_lifetime',
            'db_time_col' => 'item_time',
            'db_username' => '',
            'db_password' => '',
            'db_connection_options' => [],
        ];

        $options = [];
        $options['client'] = $serviceName;

        $mockContainer = $this->createMock(ContainerInterface::class);

        $mockContainer->expects($this->once())
            ->method('has')
            ->willReturn(true);

        $mockContainer->expects($this->once())
            ->method('get')
            ->with($this->equalTo($serviceName))
            ->willReturn($client);

        $mockAdapter = $this->getMockBuilder(PdoAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $factory = $this->getMockBuilder(PdoAdapterFactory::class)
            ->onlyMethods(['getAdapter'])
            ->getMock();

        $factory->setContainer($mockContainer);

        $factory->expects($this->once())
            ->method('getAdapter')
            ->with(
                $this->equalTo($client),
                $this->equalTo($namespace),
                $this->equalTo($lifetime),
                $this->equalTo($expected)
            )->willReturn($mockAdapter);

        $result = $factory->__invoke($options);
        $this->assertEquals($mockAdapter, $result);
    }

    public function testInvokeWithNoClientOrDsn(): void
    {
        $this->expectException(InvalidConfigException::class);

        $factory = new PdoAdapterFactory();
        $factory->__invoke([]);
    }
}
