<?php

declare(strict_types=1);

namespace Zfegg\Psr11SymfonyCache\Test;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\DependencyInjection\Container as SymfonyContainer;
use Zfegg\Psr11SymfonyCache\CacheFactory;
use Zfegg\Psr11SymfonyCache\Exception\InvalidConfigException;
use Zfegg\Psr11SymfonyCache\Exception\InvalidContainerException;
use Zfegg\Psr11SymfonyCache\Exception\MissingConfigException;

/**
 * @covers \Zfegg\Psr11SymfonyCache\CacheFactory
 */
class CacheFactoryTest extends TestCase
{
    private $config = [
        'cache' => [
            'default' => ['type' => 'array'],
            'some-key' => ['type' => 'array'],
            'some-other-key' => ['type' => 'array'],
        ]
    ];

    public function testGetConfigArraySymfony()
    {
        $mockContainer = $this->getMockBuilder(SymfonyContainer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockContainer->expects($this->once())
            ->method('hasParameter')
            ->with($this->equalTo('cache'))
            ->willReturn(true);

        $mockContainer->expects($this->once())
            ->method('getParameter')
            ->with($this->equalTo('cache'))
            ->willReturn($this->config['cache']);

        $factory = new CacheFactory('some-key');
        $result = $factory($mockContainer);

        $this->assertInstanceOf(ArrayAdapter::class, $result);
    }

    public function testGetConfigArrayZend()
    {
        $mockContainer = $this->createMock(ContainerInterface::class);

        $hasMap = [
            ['config', true],
            ['settings', false],
        ];

        $mockContainer->expects($this->atLeastOnce())
            ->method('has')
            ->willReturnMap($hasMap);

        $mockContainer->expects($this->once())
            ->method('get')
            ->with($this->equalTo('config'))
            ->willReturn($this->config);

        $factory = new CacheFactory();
        $result = $factory($mockContainer);

        $this->assertInstanceOf(ArrayAdapter::class, $result);
    }

    public function testGetConfigArraySlim()
    {
        $mockContainer = $this->createMock(ContainerInterface::class);

        $hasMap = [
            ['config', false],
            ['settings', true],
        ];

        $mockContainer->expects($this->atLeastOnce())
            ->method('has')
            ->willReturnMap($hasMap);

        $mockContainer->expects($this->once())
            ->method('get')
            ->with($this->equalTo('settings'))
            ->willReturn($this->config);

        $factory = new CacheFactory();
        $result = $factory($mockContainer);

        $this->assertInstanceOf(ArrayAdapter::class, $result);
    }

    public function testGetConfigArrayMissing()
    {
        $this->expectException(MissingConfigException::class);
        $mockContainer = $this->createMock(ContainerInterface::class);

        $hasMap = [
            ['config', false],
            ['settings', false],
        ];

        $mockContainer->expects($this->atLeastOnce())
            ->method('has')
            ->willReturnMap($hasMap);

        $mockContainer->expects($this->never())
            ->method('get');

        $factory = new CacheFactory();
        $result = $factory($mockContainer);

        $this->assertInstanceOf(ArrayAdapter::class, $result);
    }

    public function testGetConfig()
    {
        $mockContainer = $this->createMock(ContainerInterface::class);

        $hasMap = [
            ['config', true],
            ['settings', false],
        ];

        $mockContainer->expects($this->atLeastOnce())
            ->method('has')
            ->willReturnMap($hasMap);

        $mockContainer->expects($this->once())
            ->method('get')
            ->with($this->equalTo('config'))
            ->willReturn($this->config);

        $factory = new CacheFactory();
        $result = $factory($mockContainer);
        $this->assertInstanceOf(ArrayAdapter::class, $result);
    }

    public function testGetConfigWithServiceNameMissingConfig()
    {
        $this->expectException(InvalidConfigException::class);
        $serviceKey = 'my-service-name';

        $mockContainer = $this->createMock(ContainerInterface::class);

        $hasMap = [
            ['config', true],
            ['settings', false],
        ];

        $mockContainer->expects($this->atLeastOnce())
            ->method('has')
            ->willReturnMap($hasMap);

        $mockContainer->expects($this->once())
            ->method('get')
            ->with($this->equalTo('config'))
            ->willReturn($this->config);

        $factory = new CacheFactory($serviceKey);
        $factory($mockContainer);
    }

    public function testCallStatic()
    {
        $service = 'someCache';
        $type = 'array';

        $config = [
            'cache' => [
                $service => [
                    'type' => $type,
                    'options' => []
                ],
            ],
        ];

        $mockContainer = $this->createMock(ContainerInterface::class);

        $mockAdapter = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $hasMap = [
            ['config', true],
            [$type, true]
        ];

        $mockContainer->expects($this->any())
            ->method('has')
            ->willReturnMap($hasMap);

        $getMap = [
            ['config', $config],
            [$type, $mockAdapter]
        ];

        $mockContainer->expects($this->any())
            ->method('get')
            ->willReturnMap($getMap);

        $result = CacheFactory::someCache($mockContainer);
        $this->assertInstanceOf(AdapterInterface::class, $result);
    }

    public function testCallStaticMissingContainer()
    {
        $this->expectException(InvalidContainerException::class);
        CacheFactory::someCache();
    }
}
