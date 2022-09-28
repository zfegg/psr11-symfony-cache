<?php

declare(strict_types=1);

namespace Zfegg\Psr11SymfonyCache\Adapter;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\PhpArrayAdapter;
use Zfegg\Psr11SymfonyCache\Exception\InvalidConfigException;
use Zfegg\Psr11SymfonyCache\Exception\MissingConfigException;

class PhpArrayAdapterFactory implements FactoryInterface, ContainerAwareInterface
{
    use ContainerTrait;

    public function __invoke(array $options): AdapterInterface
    {
        $backupCache = (string) ($options['backupCache'] ?? null);
        $filePath = (string) ($options['filePath'] ?? '');

        if (empty($backupCache)) {
            throw new MissingConfigException(
                'A backup cache service is required for the php array adapter'
            );
        }

        if (empty($filePath)) {
            throw new MissingConfigException(
                'A file path is required for the php array adapter'
            );
        }

        if (!$this->getContainer()->has($backupCache)) {
            throw new InvalidConfigException(
                'No service found by the name: ' . $backupCache
            );
        }

        /** @var AdapterInterface $cacheService */
        $cacheService = $this->getContainer()->get($backupCache);

        return new PhpArrayAdapter($filePath, $cacheService);
    }
}
