<?php

declare(strict_types=1);

namespace Zfegg\Psr11SymfonyCache\Adapter;

use Doctrine\Common\Cache\CacheProvider;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\DoctrineAdapter;
use Zfegg\Psr11SymfonyCache\Exception\MissingConfigException;

class DoctrineAdapterFactory implements FactoryInterface, ContainerAwareInterface
{
    use ContainerTrait;

    public function __invoke(array $options): AdapterInterface
    {
        $provider = $options['provider'] ?? '';

        if (empty($provider)) {
            throw new MissingConfigException('Missing Doctrine Cache Provider Service Name');
        }

        $provider = $this->getProvider($provider);
        $namespace = (string) ($options['namespace'] ?? '');
        $maxLifetime = (int) ($options['maxLifetime'] ?? 0);

        return $this->getAdapter($provider, $namespace, $maxLifetime);
    }

    public function getAdapter(
        CacheProvider $provider,
        string $namespace,
        int $maxLifetime
    ): DoctrineAdapter {
        return new DoctrineAdapter($provider, $namespace, $maxLifetime);
    }

    public function getProvider(string $provider): CacheProvider
    {
        return $this->getContainer()->get($provider);
    }
}
