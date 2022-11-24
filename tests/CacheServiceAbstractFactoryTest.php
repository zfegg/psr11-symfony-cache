<?php

namespace Zfegg\Psr11SymfonyCache\Test;

use Laminas\ServiceManager\ServiceManager;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use PHPUnit\Framework\TestCase;
use Zfegg\Psr11SymfonyCache\ConfigProvider;

class CacheServiceAbstractFactoryTest extends TestCase
{
    public function testInvoke()
    {
        $config = (new ConfigProvider())();
        $services = new ServiceManager($config['dependencies']);
        $services->setService('config', [
            'cache' => [
                'array' => [
                    'type' => 'array',
                ],
            ],
        ]);

        $cache = $services->get('cache.array');
        self::assertInstanceOf(CacheItemPoolInterface::class, $cache);

        $simpleCache = $services->get('simple-cache.array');
        self::assertInstanceOf(CacheInterface::class, $simpleCache);
    }
}
