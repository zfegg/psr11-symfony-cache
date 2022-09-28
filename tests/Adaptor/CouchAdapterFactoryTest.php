<?php

declare(strict_types=1);

namespace Zfegg\Psr11SymfonyCache\Test\Adaptor;

use CouchbaseBucket;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\CouchbaseBucketAdapter;
use Symfony\Component\Cache\Exception\CacheException;
use Zfegg\Psr11SymfonyCache\Adapter\CouchbaseAdapterFactory;
use Zfegg\Psr11SymfonyCache\Exception\MissingConfigException;

/**
 * @covers \Zfegg\Psr11SymfonyCache\Adapter\CouchbaseAdapterFactory
 */
class CouchAdapterFactoryTest extends TestCase
{
    /** @var CouchbaseAdapterFactory */
    protected $factory;

    /** @var MockObject|ContainerInterface */
    protected $mockContainer;

    /** @var MockObject|AdapterInterface */
    protected $mockCache;

    protected function setUp(): void
    {
        $this->mockContainer = $this->createMock(ContainerInterface::class);
        $this->factory = new CouchbaseAdapterFactory();
        $this->factory->setContainer($this->mockContainer);
        $this->assertInstanceOf(CouchbaseAdapterFactory::class, $this->factory);
    }

    public function testConstructor(): void
    {
    }

    public function testCreateConnection(): void
    {
        try {
            $dsn = 'couchbase://localhost';

            $result = $this->factory->createConnection($dsn, []);
            $this->assertInstanceOf(CouchbaseBucket::class, $result);
        } catch (CacheException $exception) {
            /* Will remove this check once sdk 3.0 is supported */
            if ($exception->getMessage() == 'Couchbase >= 2.6.0 < 3.0.0 is required.') {
                $this->markTestSkipped($exception->getMessage() . ' Test Skipped.');
            }
        }
    }

    public function testGetClient(): void
    {
        $params = [
            'client' => null,
            'dsn' => 'some-dsn',
            'operationTimeout' => 1,
            'configTimeout' => 2,
            'configNodeTimeout' => 3,
            'viewTimeout' => 4,
            'httpTimeout' => 5,
            'configDelay' => 6,
            'htconfigIdleTimeout' => 7,
            'durabilityInterval' => 8,
            'durabilityTimeout' => 9,
            'username' => 'some-user',
            'password' => 'password',
        ];

        $expected = $params;
        unset($expected['client'], $expected['dsn']);

        $factory = $this->getMockBuilder(CouchbaseAdapterFactory::class)
            ->onlyMethods(['createConnection'])
            ->getMock();

        $mockService = $this->getMockBuilder(CouchbaseBucket::class)
            ->disableOriginalConstructor()
            ->getMock();

        $factory->setContainer($this->mockContainer);

        $this->mockContainer->expects($this->never())
            ->method('get');

        $factory->expects($this->once())
            ->method('createConnection')
            ->with(
                $this->equalTo($params['dsn']),
                $this->equalTo($expected)
            )->willReturn($mockService);

        $result = $factory->getClient($params);
        $this->assertEquals($mockService, $result);
    }

    public function testGetClientWithDefaults(): void
    {
        $params = [
            'client' => null,
            'dsn' => 'some-dsn',
        ];

        $expected = [
            'operationTimeout' => 2500000,
            'configTimeout' => 5000000,
            'configNodeTimeout' => 2000000,
            'viewTimeout' => 75000000,
            'httpTimeout' => 75000000,
            'configDelay' => 10000,
            'htconfigIdleTimeout' => 4294967295,
            'durabilityInterval' => 100000,
            'durabilityTimeout' => 5000000,
        ];

        $factory = $this->getMockBuilder(CouchbaseAdapterFactory::class)
            ->onlyMethods(['createConnection'])
            ->getMock();

        $mockService = $this->getMockBuilder(CouchbaseBucket::class)
            ->disableOriginalConstructor()
            ->getMock();

        $factory->setContainer($this->mockContainer);

        $this->mockContainer->expects($this->never())
            ->method('get');

        $factory->expects($this->once())
            ->method('createConnection')
            ->with(
                $this->equalTo($params['dsn']),
                $this->equalTo($expected)
            )->willReturn($mockService);

        $result = $factory->getClient($params);
        $this->assertEquals($mockService, $result);
    }

    public function testGetClientNoUsernamePassword(): void
    {
        $params = [
            'client' => null,
            'dsn' => 'some-dsn',
            'operationTimeout' => 1,
            'configTimeout' => 2,
            'configNodeTimeout' => 3,
            'viewTimeout' => 4,
            'httpTimeout' => 5,
            'configDelay' => 6,
            'htconfigIdleTimeout' => 7,
            'durabilityInterval' => 8,
            'durabilityTimeout' => 9
        ];

        $expected = $params;
        unset($expected['client'], $expected['dsn']);

        $factory = $this->getMockBuilder(CouchbaseAdapterFactory::class)
            ->onlyMethods(['createConnection'])
            ->getMock();

        $mockService = $this->getMockBuilder(CouchbaseBucket::class)
            ->disableOriginalConstructor()
            ->getMock();

        $factory->setContainer($this->mockContainer);

        $this->mockContainer->expects($this->never())
            ->method('get');

        $factory->expects($this->once())
            ->method('createConnection')
            ->with(
                $this->equalTo($params['dsn']),
                $this->equalTo($expected)
            )->willReturn($mockService);

        $result = $factory->getClient($params);
        $this->assertEquals($mockService, $result);
    }

    public function testGetClientNoClientDsnOrConnectInfoProvided(): void
    {
        $this->expectException(MissingConfigException::class);

        $this->factory->getClient([]);
    }

    public function testGetClientFromService(): void
    {
        $service = 'some-service';

        $options = ['client' => $service];

        $mockService = $this->getMockBuilder(CouchbaseBucket::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockContainer->expects($this->once())
            ->method('get')
            ->with(
                $this->equalTo($service)
            )->willReturn($mockService);

        $result = $this->factory->getClient($options);

        $this->assertEquals($mockService, $result);
    }

    public function testGetAdapter(): void
    {
        $mockService = $this->getMockBuilder(CouchbaseBucket::class)
            ->disableOriginalConstructor()
            ->getMock();

        $namespace = 'some-namespace';
        $lifetime = 39;

        try {
            $result = $this->factory->getAdapter($mockService, $namespace, $lifetime);
            $this->assertInstanceOf(CouchbaseBucketAdapter::class, $result);
        } catch (CacheException $exception) {
            /* Will remove this check once sdk 3.0 is supported */
            if ($exception->getMessage() == 'Couchbase >= 2.6.0 < 3.0.0 is required.') {
                $this->markTestSkipped($exception->getMessage() . ' Test Skipped.');
            }
        }
    }

    public function testInvoke(): void
    {
        $mockClient = $this->getMockBuilder(CouchbaseBucket::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockService = $this->getMockBuilder(CouchbaseBucketAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $namespace = 'some-namespace';
        $lifetime = 39;

        $options = [
            'namespace' => $namespace,
            'maxLifetime' => $lifetime
        ];

        $factory = $this->getMockBuilder(CouchbaseAdapterFactory::class)
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
            )->willReturn($mockService);

        $result = $factory->__invoke($options);
        $this->assertEquals($mockService, $result);
    }

    public function testInvokeWithDefaults(): void
    {
        $mockClient = $this->getMockBuilder(CouchbaseBucket::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockService = $this->getMockBuilder(CouchbaseBucketAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $namespace = '';
        $lifetime = 0;

        $options = [];

        $factory = $this->getMockBuilder(CouchbaseAdapterFactory::class)
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
            )->willReturn($mockService);

        $result = $factory->__invoke($options);
        $this->assertEquals($mockService, $result);
    }
}
