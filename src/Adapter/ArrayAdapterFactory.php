<?php

declare(strict_types=1);

namespace Zfegg\Psr11SymfonyCache\Adapter;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class ArrayAdapterFactory implements FactoryInterface
{
    public function __invoke(array $options): AdapterInterface
    {
        $defaultLifetime = (int) ($options['defaultLifetime'] ?? 0);
        $storeSerialized = (bool) ($options['storeSerialized'] ?? true);
        $maxLifetime = (int) ($options['maxLifetime'] ?? 0);
        $maxItems = (int) ($options['maxItems'] ?? 0);

        return $this->getAdapter($defaultLifetime, $storeSerialized, $maxLifetime, $maxItems);
    }

    protected function getAdapter(
        int $defaultLifetime,
        bool $storeSerialized,
        int $maxLifetime,
        int $maxItems
    ): ArrayAdapter {
        return new ArrayAdapter($defaultLifetime, $storeSerialized, $maxLifetime, $maxItems);
    }
}
