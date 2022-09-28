<?php

declare(strict_types=1);

namespace Zfegg\Psr11SymfonyCache\Adapter;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\ProxyAdapter;
use Zfegg\Psr11SymfonyCache\Exception\InvalidConfigException;
use Zfegg\Psr11SymfonyCache\Exception\MissingConfigException;

class ProxyAdapterFactory implements FactoryInterface, ContainerAwareInterface
{
    use ContainerTrait;

    public function __invoke(array $options): AdapterInterface
    {
        $psr6Service = (string) ($options['psr6Service'] ?? null);

        if (empty($psr6Service)) {
            throw new MissingConfigException(
                'A PSR6 service is required for the php array adapter'
            );
        }

        if (!$this->getContainer()->has($psr6Service)) {
            throw new InvalidConfigException(
                'No PSR6 service found by the name: ' . $psr6Service
            );
        }

        /** @var CacheItemPoolInterface $cacheService */
        $cacheService = $this->getContainer()->get($psr6Service);
        $namespace = (string) ($options['namespace'] ?? '');
        $defaultLifetime = (int) ($options['defaultLifetime'] ?? 0);

        return $this->getAdapter($cacheService, $namespace, $defaultLifetime);
    }

    public function getAdapter(
        CacheItemPoolInterface $cacheService,
        string $namespace,
        int $defaultLifetime
    ): ProxyAdapter {
        return new ProxyAdapter($cacheService, $namespace, $defaultLifetime);
    }
}
