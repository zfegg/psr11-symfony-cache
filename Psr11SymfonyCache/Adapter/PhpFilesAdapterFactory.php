<?php

declare(strict_types=1);

namespace Zfegg\Psr11SymfonyCache\Adapter;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\PhpFilesAdapter;

class PhpFilesAdapterFactory implements FactoryInterface
{
    public function __invoke(array $options): AdapterInterface
    {
        $namespace = (string) ($options['namespace'] ?? '');
        $defaultLifetime = (int) ($options['defaultLifetime'] ?? 0);
        $directory = (string) ($options['directory'] ?? '');

        return $this->getAdapter($namespace, $defaultLifetime, $directory);
    }

    public function getAdapter(
        string $namespace,
        int $defaultLifetime,
        string $directory
    ): PhpFilesAdapter {
        return new PhpFilesAdapter($namespace, $defaultLifetime, $directory);
    }
}
