<?php

namespace Zfegg\Psr11SymfonyCache;

use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Psr16Cache;

class CacheServiceAbstractFactory implements AbstractFactoryInterface
{
    private const CONFIG_KEY = 'cache';

    /**
     * @inheritdoc
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        if (strpos($requestedName, 'cache.') !== 0 && strpos($requestedName, 'simple-cache.') !== 0) {
            return false;
        }

        if (! $container->has('config')) {
            return false;
        }
        [, $name] = explode('.', $requestedName, 2);
        $config = $container->get('config')[self::CONFIG_KEY] ?? [];
        return isset($config[$name]) && is_array($config[$name]);
    }

    /**
     * @inheritdoc
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
    {
        [$type, $name] = explode('.', $requestedName, 2);

        if ($type == 'simple-cache') {
            $cache = $container->get('cache.' . $name);
            return new Psr16Cache($cache);
        }

        return (new CacheFactory($name))($container);
    }
}
