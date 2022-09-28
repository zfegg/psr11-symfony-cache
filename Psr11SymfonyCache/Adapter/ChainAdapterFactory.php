<?php

declare(strict_types=1);

namespace Zfegg\Psr11SymfonyCache\Adapter;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\ChainAdapter;
use Zfegg\Psr11SymfonyCache\Exception\InvalidConfigException;

class ChainAdapterFactory implements FactoryInterface, ContainerAwareInterface
{
    use ContainerTrait;

    public function __invoke(array $options): AdapterInterface
    {
        $adapters = $options['adapters'] ?? [];

        if (!is_array($adapters)) {
            throw new InvalidConfigException('Chain adapters must be an array of cache service names');
        }

        $adapters = $this->getAdapters($adapters);

        if (empty($adapters)) {
            throw new InvalidConfigException('Unable to locate caches to chain from config');
        }

        $maxLifetime = (int) ($options['maxLifetime'] ?? 0);

        return $this->getChain($adapters, $maxLifetime);
    }

    public function getChain(array $adapters, int $maxLifetime): ChainAdapter
    {
        return new ChainAdapter($adapters, $maxLifetime);
    }

    public function getAdapters(array $adapters): array
    {
        $return = [];

        foreach ($adapters as $adapter) {
            $return[] = $this->getAdapter($adapter);
        }

        return $return;
    }

    public function getAdapter(string $adapter): AdapterInterface
    {
        return $this->getContainer()->get($adapter);
    }
}
