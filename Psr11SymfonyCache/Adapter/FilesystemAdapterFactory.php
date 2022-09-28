<?php

declare(strict_types=1);

namespace Zfegg\Psr11SymfonyCache\Adapter;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class FilesystemAdapterFactory implements FactoryInterface
{
    public function __invoke(array $options): AdapterInterface
    {
        $directory = (string) ($options['directory'] ?? '');
        $namespace = (string) ($options['namespace'] ?? '');
        $defaultLifetime = (int) ($options['defaultLifetime'] ?? 0);

        return $this->getAdapter($namespace, $defaultLifetime, $directory);
    }

    public function getAdapter(
        string $namespace,
        int $defaultLifetime,
        string $directory
    ): FilesystemAdapter {
        return new FilesystemAdapter($namespace, $defaultLifetime, $directory);
    }
}
