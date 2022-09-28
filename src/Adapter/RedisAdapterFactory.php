<?php

declare(strict_types=1);

namespace Zfegg\Psr11SymfonyCache\Adapter;

use Predis\ClientInterface;
use Redis;
use RedisCluster;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Zfegg\Psr11SymfonyCache\Exception\MissingConfigException;

class RedisAdapterFactory implements FactoryInterface, ContainerAwareInterface
{
    use ContainerTrait;

    public function __invoke(array $options): AdapterInterface
    {
        $client = $this->getClient($options);
        $namespace = (string) ($options['namespace'] ?? '');
        $maxLifetime = (int) ($options['maxLifetime'] ?? 0);

        return $this->getAdapter($client, $namespace, $maxLifetime);
    }

    public function getAdapter(
        $client,
        string $namespace,
        int $maxLifetime
    ): RedisAdapter {
        return new RedisAdapter($client, $namespace, $maxLifetime);
    }

    /**
     * @param array $options
     * @return ClientInterface|Redis|RedisCluster
     */
    public function getClient(array $options)
    {
        if (
            !empty($options['client'])
            && $this->getContainer()->has($options['client'])
        ) {
            return $this->getContainer()->get($options['client']);
        }

        if (
            empty($options['dsn'])
            || !is_string($options['dsn'])
        ) {
            throw new MissingConfigException('A Redis service name, DSNs or connection information not found');
        }

        $dsn = (string) $options['dsn'];


        $params = [];
        $params['class'] = (string) ($options['class'] ?? '\Redis');
        $params['compression'] = (bool) ($options['compression'] ?? true);
        $params['lazy'] = (bool) ($options['lazy'] ?? false);
        $params['persistent'] = (int) ($options['persistent'] ?? 0);
        $params['persistent_id'] = (string) ($options['persistent_id'] ?? '');
        $params['read_timeout'] = (int) ($options['read_timeout'] ?? 0);
        $params['retry_interval'] = (int) ($options['retry_interval'] ?? 0);
        $params['tcp_keepalive'] = (int) ($options['tcp_keepalive'] ?? 0);
        $params['timeout'] = (int) ($options['timeout'] ?? 30);

        return $this->getConnection($dsn, $params);
    }

    /**
     * @param string $dsn
     * @param array $params
     * @return ClientInterface|Redis|RedisCluster
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function getConnection(
        string $dsn,
        array $params
    ) {
        return RedisAdapter::createConnection($dsn, $params);
    }
}
