<?php

declare(strict_types=1);

namespace Zfegg\Psr11SymfonyCache\Adapter;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\PdoAdapter;
use Zfegg\Psr11SymfonyCache\Exception\InvalidConfigException;

class PdoAdapterFactory implements FactoryInterface, ContainerAwareInterface
{
    use ContainerTrait;

    public function __invoke(array $options): AdapterInterface
    {
        $client = $options['client'] ?? null;

        if (!$client) {
            throw new InvalidConfigException('Missing client service name or dsn');
        }

        if ($this->getContainer()->has($client)) {
            $client = $this->getContainer()->get($client);
        }

        $params = [];
        $params['db_table'] = (string) ($options['db_table'] ?? 'cache_items');
        $params['db_id_col'] = (string) ($options['db_id_col'] ?? 'item_id');
        $params['db_data_col'] = (string) ($options['db_data_col'] ?? 'item_id');
        $params['db_lifetime_col'] = (string) ($options['db_lifetime_col'] ?? 'item_lifetime');
        $params['db_time_col'] = (string) ($options['db_time_col'] ?? 'item_time');
        $params['db_username'] = (string) ($options['db_username'] ?? '');
        $params['db_password'] = (string) ($options['db_password'] ?? '');
        $params['db_connection_options'] = (array) ($options['db_connection_options'] ?? []);

        $namespace = (string) ($options['namespace'] ?? '');
        $maxLifetime = (int) ($options['maxLifetime'] ?? 0);

        return $this->getAdapter($client, $namespace, $maxLifetime, $params);
    }

    public function getAdapter(
        $client,
        string $namespace,
        int $maxLifetime,
        array $params
    ): PdoAdapter {
        return new PdoAdapter($client, $namespace, $maxLifetime, $params);
    }
}
