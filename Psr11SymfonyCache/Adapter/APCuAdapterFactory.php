<?php

declare(strict_types=1);

namespace Zfegg\Psr11SymfonyCache\Adapter;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\ApcuAdapter;

class APCuAdapterFactory implements FactoryInterface
{
    public function __invoke(array $options): AdapterInterface
    {
        $namespace = (string) ($options['namespace'] ?? '');
        $defaultLifetime = (int) ($options['defaultLifetime'] ?? 0);
        $version = (string) ($options['version'] ?? '');

        return $this->getAdapter($namespace, $defaultLifetime, $version);
    }

    protected function getAdapter(
        string $namespace,
        int $defaultLifetime,
        string $version
    ): ApcuAdapter {

        if (empty($version)) {
            return new ApcuAdapter($namespace, $defaultLifetime);
        }

        return new ApcuAdapter($namespace, $defaultLifetime, $version);
    }
}
