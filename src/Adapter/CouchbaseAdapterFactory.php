<?php

declare(strict_types=1);

namespace Zfegg\Psr11SymfonyCache\Adapter;

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\CouchbaseBucketAdapter;
use Zfegg\Psr11SymfonyCache\Exception\MissingConfigException;

class CouchbaseAdapterFactory implements FactoryInterface, ContainerAwareInterface
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
    ): CouchbaseBucketAdapter {
        return new CouchbaseBucketAdapter($client, $namespace, $maxLifetime);
    }

    public function getClient(array $options)
    {
        if (!empty($options['client'])) {
            return $this->getContainer()->get($options['client']);
        }

        if (
            empty($options['dsn'])
            && (empty($options['username']) || empty($options['password']))
        ) {
            throw new MissingConfigException('A Couchbase service name, DSNs or connection information not found');
        }

        $dsn = [];

        if (!empty($options['dsn'])) {
            $dsn = $options['dsn'];
        }

        $username = $options['username'] ?? null;
        $password = $options['password'] ?? null;

        $params = [];
        $params['operationTimeout'] = (int) ($options['operationTimeout'] ?? 2500000);
        $params['configTimeout'] = (int) ($options['configTimeout'] ?? 5000000);
        $params['configNodeTimeout'] = (int) ($options['configNodeTimeout'] ?? 2000000);
        $params['viewTimeout'] = (int) ($options['viewTimeout'] ?? 75000000);
        $params['httpTimeout'] = (int) ($options['httpTimeout'] ?? 75000000);
        $params['configDelay'] = (int) ($options['configDelay'] ?? 10000);
        $params['htconfigIdleTimeout'] = (int) ($options['htconfigIdleTimeout'] ?? 4294967295);
        $params['durabilityInterval'] = (int) ($options['durabilityInterval'] ?? 100000);
        $params['durabilityTimeout'] = (int) ($options['durabilityTimeout'] ?? 5000000);

        if (!empty($username)) {
            $params['username'] = (string) $username;
        }

        if (!empty($password)) {
            $params['password'] = (string) $password;
        }

        return $this->createConnection($dsn, $params);
    }

    /** @SuppressWarnings(PHPMD.StaticAccess) */
    public function createConnection(string $dsn, array $params)
    {
        return CouchbaseBucketAdapter::createConnection($dsn, $params);
    }
}
