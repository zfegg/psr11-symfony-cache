<?php

declare(strict_types=1);

namespace Zfegg\Psr11SymfonyCache\Adapter;

use Memcached;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\MemcachedAdapter;
use Zfegg\Psr11SymfonyCache\Exception\MissingConfigException;

class MemcachedAdapterFactory implements FactoryInterface, ContainerAwareInterface
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
        Memcached $client,
        string $namespace,
        int $maxLifetime
    ): MemcachedAdapter {
        return new MemcachedAdapter($client, $namespace, $maxLifetime);
    }

    public function getClient(array $options): Memcached
    {
        if (!empty($options['client'])) {
            return $this->getContainer()->get($options['client']);
        }

        if (empty($options['dsn'])) {
            throw new MissingConfigException('A Memcached service name, or DSNs are required');
        }

        $dsn = $options['dsn'];

        $params = [];
        $params['auto_eject_hosts'] = (bool) ($options['auto_eject_hosts'] ?? false);
        $params['buffer_writes'] = (bool) ($options['buffer_writes'] ?? false);
        $params['compression'] = (bool) ($options['compression'] ?? true);
        $params['compression_type'] = (string) ($options['compression_type'] ?? '');
        $params['connect_timeout'] = (int) ($options['connect_timeout'] ?? 1000);
        $params['distribution'] = (string) ($options['distribution'] ?? 'consistent');
        $params['hash'] = (string) ($options['hash'] ?? 'md5');
        $params['libketama_compatible'] = (bool) ($options['libketama_compatible'] ?? true);
        $params['no_block'] = (bool) ($options['no_block'] ?? true);
        $params['number_of_replicas'] = (int) ($options['number_of_replicas'] ?? 0);
        $params['prefix_key'] = (string) ($options['prefix_key'] ?? '');
        $params['poll_timeout'] = (int) ($options['poll_timeout'] ?? 1000);
        $params['randomize_replica_read'] = (bool) ($options['randomize_replica_read'] ?? false);
        $params['recv_timeout'] = (int) ($options['recv_timeout'] ?? 0);
        $params['retry_timeout'] = (int) ($options['retry_timeout'] ?? 0);
        $params['send_timeout'] = (int) ($options['send_timeout'] ?? 0);
        $params['serializer'] = (string) ($options['serializer'] ?? 'php');
        $params['server_failure_limit'] = (int) ($options['server_failure_limit'] ?? 0);
        $params['socket_recv_size'] = (int) ($options['socket_recv_size'] ?? 0);
        $params['tcp_keepalive'] = (bool) ($options['tcp_keepalive'] ?? false);
        $params['tcp_nodelay'] = (bool) ($options['tcp_nodelay'] ?? false);
        $params['use_udp'] = (bool) ($options['use_udp'] ?? false);
        $params['verify_key'] = (bool) ($options['verify_key'] ?? false);

        return $this->getConnection($dsn, $params);
    }

    /** @SuppressWarnings(PHPMD.StaticAccess) */
    public function getConnection(
        string $dsn,
        array $params
    ): Memcached {
        return MemcachedAdapter::createConnection($dsn, $params);
    }
}
