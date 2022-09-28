[![codecov](https://codecov.io/gl/zfegg/psr11-symfony-cache/branch/master/graph/badge.svg?token=4qrIUhkR2g)](https://codecov.io/gl/zfegg/psr11-symfony-cache)
[![pipeline status](https://gitlab.com/zfegg/psr11-symfony-cache/badges/master/pipeline.svg)](https://gitlab.com/zfegg/psr11-symfony-cache/-/commits/master)
# PSR-11 Symfony Cache

[Symfony Cache Component](https://symfony.com/doc/current/components/cache.html) Factories for PSR-11.

#### Table of Contents
- [Installation](#installation)
- [Usage](#usage)
- [Containers](#containers)
  - [Pimple](#pimple-example)
  - [Laminas Service Manager](#laminas-service-manager)
- [Frameworks](#frameworks)
  - [Mezzio](#mezzio)
  - [Laminas](#laminas)
  - [Slim](#slim)
- [Configuration](#configuration)
  - [Minimal Configuration](#minimal-configuration)
    - [Example](#minimal-example)
  - [Full Configuration](#full-configuration)
    - [Example](#full-example)
  - [Adaptors](#adaptors)
    - [APCu](#apcu)
    - [Array](#array)
    - [Chain](#chain)
    - [Couchbase](#couchbase)
    - [Doctrine](#doctrine)
    - [Filesystem](#filesystem)
    - [Memcached](#memcached)
    - [PDO & Doctrine DBAL](#pdo-and-doctrine-dbal)
    - [PHP Array](#php-array)
    - [PHP Files](#php-files)
    - [Proxy](#proxy)
    - [Redis](#redis)
    

# Installation

```bash
composer require zfegg/psr11-symfony-cache
```

# Usage

```php
<?php
/** @var \Symfony\Component\Cache\Adapter\AdapterInterface $cache */
$cache = $container->get('other');

// The callable will only be executed on a cache miss.
$value = $cache->get('my_cache_key', function (\Symfony\Contracts\Cache\ItemInterface $item) {
    $item->expiresAfter(3600);

    // ... do some HTTP request or heavy computations
    $computedValue = 'foobar';

    return $computedValue;
});

echo $value; // 'foobar'
```

Additional info can be found in the [documentation](https://symfony.com/doc/current/components/cache.html)

# Containers
Any PSR-11 container wil work.  In order to do that you will need to add configuration
and register a new service that points to `Zfegg\Psr11SymfonyCache\CacheFactory` 

Below are some specific container examples to get you started

## Pimple Example
```php
// Create Container
$container = new \Xtreamwayz\Pimple\Container([
    // Cache using the default keys.
    'cache' => new \Zfegg\Psr11SymfonyCache\CacheFactory(),
    
    // Second cache using a different cache configuration
    'other' => function($c) {
        return \Zfegg\Psr11SymfonyCache\CacheFactory::other($c);
    },
    
    // Config
    'config' => [
        'symfonyCache' => [
            // At the bare minimum you must include a default adaptor.
            'default' => [  
                'type' => '',
                'options' => [],
            ],
            
            // Some other Adaptor.  Keys are the names for each adaptor
            'someOtherAdaptor' => [
                'type' => 'local',
                'options' => [
                    'root' => '/tmp/pimple'
                ],
            ],
        ],
    ]
]);

/** @var \Symfony\Component\Cache\Adapter\AdapterInterface $cache */
$cache = $container->get('other');
// The callable will only be executed on a cache miss.
$value = $cache->get('my_cache_key', function (\Symfony\Contracts\Cache\ItemInterface $item) {
    $item->expiresAfter(3600);

    // ... do some HTTP request or heavy computations
    $computedValue = 'foobar';

    return $computedValue;
});

echo $value; // 'foobar'

// ... and to remove the cache key
$cache->delete('my_cache_key');

```

## Laminas Service Manager

```php
// Create the container and define the services you'd like to use
$container = new \Zend\ServiceManager\ServiceManager([
    'factories' => [
        // Cache using the default keys.
        'fileSystem' => \Zfegg\Psr11SymfonyCache\CacheFactory::class,
        
        // Second cache using a different cache configuration
        'other' => [\Zfegg\Psr11SymfonyCache\CacheFactory::class, 'other'],
    ],
]);

// Config
$container->setService('config', [
    'symfonyCache' => [
        // At the bare minimum you must include a default adaptor.
        'default' => [  
            'type' => '',
            'options' => [],
        ],
        
        // Some other Adaptor.  Keys are the names for each adaptor
        'someOtherAdaptor' => [
            'type' => 'local',
            'options' => [
                'root' => '/tmp/pimple'
            ],
        ],
    ],
]);

/** @var \Symfony\Component\Cache\Adapter\AdapterInterface $cache */
$cache = $container->get('other');
// The callable will only be executed on a cache miss.
$value = $cache->get('my_cache_key', function (\Symfony\Contracts\Cache\ItemInterface $item) {
    $item->expiresAfter(3600);

    // ... do some HTTP request or heavy computations
    $computedValue = 'foobar';

    return $computedValue;
});

echo $value; // 'foobar'

// ... and to remove the cache key
$cache->delete('my_cache_key');
```

# Frameworks
Any framework that use a PSR-11 should work fine.   Below are some specific framework examples to get you started

## Mezzio
You'll need to add configuration and register the services you'd like to use.  There are number of ways to do that
but the recommended way is to create a new config file `config/autoload/cache.global.php`

### Configuration
config/autoload/cache.global.php
```php
<?php
return [
    'dependencies' => [
        'factories' => [
            // Cache using the default keys.
            'fileSystem' => \Zfegg\Psr11SymfonyCache\CacheFactory::class,
            
            // Second cache using a different filesystem configuration
            'someOtherAdaptor' => [\Zfegg\Psr11SymfonyCache\CacheFactory::class, 'someOtherAdaptor'],
        ],
    ],
    
    'symfonyCache' => [
        // At the bare minimum you must include a default adaptor.
        'default' => [  
            'type' => '',
            'options' => [],
        ],
        
        // Some other Adaptor.  Keys are the names for each adaptor
        'someOtherAdaptor' => [
            'type' => 'local',
            'options' => [
                'root' => '/tmp/pimple'
            ],
        ],
    ],
];
```

## Laminas
You'll need to add configuration and register the services you'd like to use.  There are number of ways to do that
but the recommended way is to create a new config file `config/autoload/cache.global.php`

### Configuration
config/autoload/cache.global.php
```php
<?php
return [
    'service_manager' => [
        'factories' => [
            // Cache using the default keys.
            'fileSystem' => \Zfegg\Psr11SymfonyCache\CacheFactory::class,
            
            // Second cache using a different configuration
            'someOtherAdaptor' => [\Zfegg\Psr11SymfonyCache\CacheFactory::class, 'someOtherAdaptor'],
        ],
    ],
    
    'symfonyCache' => [
        // At the bare minimum you must include a default adaptor.
        'default' => [  
            'type' => '',
            'options' => [],
        ],
        
        // Some other Adaptor.  Keys are the names for each adaptor
        'someOtherAdaptor' => [
            'type' => 'local',
            'options' => [
                'root' => '/tmp/pimple'
            ],
        ],
    ],
];
```

## Slim

public/index.php
```php
<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Symfony\Component\Cache\Adapter\AdapterInterface;
use \Symfony\Contracts\Cache\ItemInterface;

require '../vendor/autoload.php';

// Add Configuration
$config = [
    'settings' => [
        'symfonyCache' => [
            // At the bare minimum you must include a default adaptor.
            'default' => [  
                'type' => '',
                'options' => [],
            ],
            
            // Some other Adaptor.  Keys are the names for each adaptor
            'someOtherAdaptor' => [
                'type' => 'local',
                'options' => [
                    'root' => '/tmp/pimple'
                ],
            ],
        ],
    ],
];

$app = new \Slim\App($config);

// Wire up the factory
$container = $app->getContainer();

// Cache using the default keys.
$container['fileSystem'] = new \Zfegg\Psr11SymfonyCache\CacheFactory();

// Second cache using a different cache configuration
$container['someOtherAdaptor'] = function ($c) {
    return \Zfegg\Psr11SymfonyCache\CacheFactory::someOtherAdaptor($c);
};


// Example usage
$app->get('/example', function (Request $request, Response $response) {
    
    /** @var AdapterInterface $cache */
    $cache = $this->get('other');
    // The callable will only be executed on a cache miss.
    $value = $cache->get('my_cache_key', function (ItemInterface $item) {
        $item->expiresAfter(3600);
    
        // ... do some HTTP request or heavy computations
        $computedValue = 'foobar';
    
        return $computedValue;
    });
    
    echo $value; // 'foobar'
    
    // ... and to remove the cache key
    $cache->delete('my_cache_key');
});

$app->run();
```

# Configuration

## Minimal Configuration
A minimal configuration would consist of at least defining one service and the "default" adaptor.

### Minimal Example (using Zend Expressive for the example)
```php
<?php

return [
    'symfonyCache' => [
        'default' => [  
            'type' => '',
            'options' => [],
        ],
    ],
];
```
Using this setup you will be using the "default" file system with the "default" adaptor.  In this
example we will be using the local file adaptor as the default.

## Full Configuration
Note: A "default" adaptor is required.

### Full Example
```php
<?php

return [
    'symfonyCache' => [
        // At the bare minimum you must include a default adaptor.
        'default' => [  
            'type' => '',
            'options' => [],
        ],
        
        // Some other Adaptor.  Keys are the names for each adaptor
        'someOtherAdaptor' => [
            'type' => 'local',
            'options' => [
                'root' => '/tmp/pimple'
            ],
        ],
    ],
];

```

### Adaptors
Example configs for supported adaptors

#### APCu
This adapter is a high-performance, shared memory cache. It can significantly increase an application’s performance, 
as its cache contents are stored in shared memory, a component appreciably faster than many others, such as the 
filesystem.

```php
<?php

return [
    'symfonyCache' => [
        'default' => [  
            'type' => 'APCu',
            'options' => [
                'namespace' => '', // Optional : a string prefixed to the keys of the items.
                'defaultLifetime' => 0, // Optional : the default lifetime (in seconds) for cache items.  Default: 0
                'version' => null, // Optional : Version of the cache items.
            ],
        ],
    ],
];
```

Docs: [APCu Cache Adapter](https://symfony.com/doc/3.4/components/cache/adapters/apcu_adapter.html)


#### Array
Generally, this adapter is useful for testing purposes, as its contents are stored in memory and 
not persisted outside the running PHP process in any way. It can also be useful while warming up 
caches, due to the getValues() method

```php
<?php

return [
    'symfonyCache' => [
        'default' => [  
            'type' => 'Array',
            'options' => [
                'defaultLifetime' => 0, // Optional : the default lifetime (in seconds) for cache items.  Default: 0
                'storeSerialized' => true, // Optional : values saved in the cache are serialized before storing them Default: true
                'maxLifetime' => 0, // Optional : the maximum lifetime (in seconds) of the entire cache.  Default: 0
                'maxItems' => 0, // Optional : the maximum number of items that can be stored in the cache.  Default: 0
            ],
        ],
    ],
];
```

Docs: [APCu Cache Adapter](https://symfony.com/doc/3.4/components/cache/adapters/apcu_adapter.html)


#### Chain
This adapter allows combining any number of the other available cache adapters. Cache items are fetched from the first
adapter containing them and cache items are saved to all the given adapters. This exposes a simple and efficient method
for creating a layered cache.

```php
<?php

return [
    'symfonyCache' => [
        'default' => [  
            'type' => 'Chain',
            'options' => [
                'adapters' => [], // Required : The ordered list of adapter service names to fetch cached items
                'namespace' => '', // Optional : a string prefixed to the keys of the items.
                'maxLifetime' => 0, // Optional : The max lifetime of items propagated from lower adapters to upper ones
            ],
        ],
    ],
];
```

Docs: [Chain Cache Adapter](https://symfony.com/doc/3.4/components/cache/adapters/chain_adapter.html)

#### Couchbase
This adapter stores the values in-memory using one (or more) Couchbase server instances. Unlike the APCu adapter, and 
similarly to the Memcached adapter, it is not limited to the current server’s shared memory; you can store contents 
independent of your PHP environment. The ability to utilize a cluster of servers to provide redundancy and/or 
fail-over is also available.

```php
<?php

return [
    'symfonyCache' => [
        'default' => [  
            'type' => 'Couchbase',
            'options' => [
                // Connection config
                // You must provide a client service, dsn(s) or connection information
                'client' => 'service-name', // Couch Service name.  Will be pulled from the container.
                'dsn' => 'string or array', // Dsn for connections.  Can be one dsn or an array of dsn's

                // Manual connection
                'username' => 'username', // Required for manual connection
                'password' => 'password', // Required for manual connection
                'operationTimeout' => 2500000, // Optional.  Default: 2500000
                'configTimeout' => 5000000, // Optional.  Default: 5000000
                'configNodeTimeout' => 2000000, // Optional.  Default: 2000000
                'viewTimeout' => 75000000, // Optional.  Default: 75000000
                'httpTimeout' => 75000000, // Optional.  Default: 75000000
                'configDelay' => 10000, // Optional.  Default: 10000
                'htconfigIdleTimeout' => 4294967295, // Optional.  Default: 4294967295
                'durabilityInterval' => 100000, // Optional.  Default: 100000
                'durabilityTimeout' => 5000000, // Optional.  Default: 5000000
                
                // Cache Config
                'namespace' => '', // Optional : a string prefixed to the keys of the items.
                'maxLifetime' => 0, // Optional : The max lifetime of items propagated from lower adapters to upper ones
            ],
        ],
    ],
];
```

Docs: [Couchbase Cache Adapter](https://symfony.com/doc/current/components/cache/adapters/couchbasebucket_adapter.html)

#### Doctrine
This adapter wraps any class extending the Doctrine Cache abstract provider, allowing you to use these providers in 
your application as if they were Symfony Cache adapters.

```php
<?php

return [
    'symfonyCache' => [
        'default' => [  
            'type' => 'Doctrine',
            'options' => [
                'provider' => 'service-name', // Required : Doctrine Cache Service name.  Will be pulled from the container.
                'namespace' => '', // Optional : a string prefixed to the keys of the items.
                'maxLifetime' => 0, // Optional : The max lifetime of items propagated from lower adapters to upper ones
            ],
        ],
    ],
];
```

Docs: [Doctrine Cache Adapter](https://symfony.com/doc/current/components/cache/adapters/doctrine_adapter.html)

#### Filesystem
This adapter offers improved application performance for those who cannot install tools like APCu or Redis in their
environment. It stores the cache item expiration and content as regular files in a collection of directories on a
locally mounted filesystem.

```php
<?php

return [
    'symfonyCache' => [
        'default' => [  
            'type' => 'Filesystem',
            'options' => [
                'directory' => '', // Optional : The main cache directory.  Default: directory is created inside the system temporary directory
                'namespace' => '', // Optional : a string prefixed to the keys of the items.
                'defaultLifetime' => 0, // Optional : the default lifetime (in seconds) for cache items.  Default: '0'
            ],
        ],
    ],
];
```

Docs: [Filesystem Cache Adapter](https://symfony.com/doc/current/components/cache/adapters/filesystem_adapter.html)

#### Memcached
This adapter stores the values in-memory using one (or more) Memcached server instances. Unlike the APCu adapter, and
similarly to the Redis adapter, it is not limited to the current server’s shared memory; you can store contents
independent of your PHP environment. The ability to utilize a cluster of servers to provide redundancy and/or
fail-over is also available.

```php
<?php

return [
    'symfonyCache' => [
        'default' => [  
            'type' => 'Memcached',
            'options' => [
                // Connection config
                // A client service, or dsn(s).  Default: localhost
                'client' => 'service-name', // Memcached service name.  Will be pulled from the container.
                'dsn' => 'string or array', // Dsn for connections.  Can be one dsn or an array of dsn's

                // Options
                'auto_eject_hosts' => false, // Optional.  Default: false
                'buffer_writes' => false, // Optional.  Default: false
                'compression' => true, // Optional.  Default: true
                'compression_type' => 'fastlz', // Optional.  Default: Varies based on flags used at compilation
                'connect_timeout' => 1000, // Optional.  Default: 1000
                'distribution' => 'consistent', // Optional.  Default: consistent
                'hash' => 'md5', // Optional.  Default: md5
                'libketama_compatible' => true, // Optional.  Default: true
                'no_block' => true, // Optional.  Default: true
                'number_of_replicas' => 0, // Optional.  Default: 0
                'prefix_key' => '', // Optional.  Default: empty string
                'poll_timeout' => 1000, // Optional.  Default: 1000
                'randomize_replica_read' => false, // Optional.  Default: false
                'recv_timeout' => 0, // Optional.  Default: 0
                'retry_timeout' => 0, // Optional.  Default: 0
                'send_timeout' => 0, // Optional.  Default: 0
                'serializer' => 'php', // Optional.  Default: php
                'server_failure_limit' => 0, // Optional.  Default: 0
                'socket_recv_size' => 0, // Optional.  Default: varies by platform and kernel
                'socket_send_size' => 0, // Optional.  Default: varies by platform and kernel
                'tcp_keepalive' => false, // Optional.  Default: false
                'tcp_nodelay' => false, // Optional.  Default: false
                'use_udp' => false, // Optional.  Default: false
                'verify_key' => false, // Optional.  Default: false
                
                // Cache Config
                'namespace' => '', // Optional : a string prefixed to the keys of the items.
                'maxLifetime' => 0, // Optional : The max lifetime of items propagated from lower adapters to upper ones
            ],
        ],
    ],
];
```

Docs: [Memcached Cache Adapter](https://symfony.com/doc/current/components/cache/adapters/memcached_adapter.html)

#### PDO and Doctrine DBAL
This adapter stores the cache items in an SQL database.

```php
<?php

return [
    'symfonyCache' => [
        'default' => [  
            'type' => 'PDO',
            'options' => [
                'client' => 'service-name', // Required: A client service, or dsn(s). 
                
                'db_table' => 'cache_items', // Optional.  The name of the table.  Default: cache_items
                'db_id_col' => 'item_id', // Optional.  The column where to store the cache id.  Default: item_id
                'db_data_col' => 'item_data', // Optional.  The column where to store the cache data.  Default: item_data
                'db_lifetime_col' => 'item_lifetime', // Optional.  The column where to store the lifetime.  Default: item_lifetime
                'db_time_col' => 'item_time', // Optional.  The column where to store the timestamp.  Default: item_time
                'db_username' => '', // Optional.  The username when lazy-connect
                'db_password' => '', // Optional.  The password when lazy-connect
                'db_connection_options' => '', // Optional.  An array of driver-specific connection options
                
                // Cache Config
                'namespace' => '', // Optional : a string prefixed to the keys of the items.
                'maxLifetime' => 0, // Optional : The max lifetime of items propagated from lower adapters to upper ones
            ],
        ],
    ],
];
```

Docs: [PDO & Doctrine DBAL Cache Adapter](https://symfony.com/doc/current/components/cache/adapters/pdo_doctrine_dbal_adapter.html)


#### PHP Array
This adapter is a high performance cache for static data (e.g. application configuration) that is optimized and
preloaded into OPcache memory storage. It is suited for any data that is mostly read-only after warmup.

```php
<?php

return [
    'symfonyCache' => [
        'default' => [  
            'type' => 'PhpArray',
            'options' => [
                'filePath' => __DIR__ . '/somefile.cache', // Required: Single file where values are cached
                'backupCache' => 'service-name', // Required: A backup cache service
            ],
        ],
    ],
];
```

Docs: [PHP Array Cache Adapter](https://symfony.com/doc/current/components/cache/adapters/php_array_cache_adapter.html)


#### PHP Files
Similarly to Filesystem Adapter, this cache implementation writes cache entries out to disk, but unlike the Filesystem
cache adapter, the PHP Files cache adapter writes and reads back these cache files as native PHP code.

```php
<?php

return [
    'symfonyCache' => [
        'default' => [  
            'type' => 'PhpFiles',
            'options' => [
                'directory' => '/some/dir/path', // Required: The main cache directory (the application needs read-write permissions on it)
                
                // Cache Config
                'namespace' => '', // Optional : a string prefixed to the keys of the items.
                'maxLifetime' => 0, // Optional : The max lifetime of items propagated from lower adapters to upper ones
            ],
        ],
    ],
];
```

Docs: [PHP Files Cache Adapter](https://symfony.com/doc/current/components/cache/adapters/php_array_cache_adapter.html)


#### Proxy
This adapter wraps a PSR-6 compliant cache item pool interface. It is used to integrate your application’s cache item
pool implementation with the Symfony Cache Component by consuming any implementation of
Psr\Cache\CacheItemPoolInterface.

It can also be used to prefix all keys automatically before storing items in the decorated pool, effectively allowing
the creation of several namespaced pools out of a single one.

```php
<?php

return [
    'symfonyCache' => [
        'default' => [  
            'type' => 'proxy',
            'options' => [
                'psr6Service' => 'service-name', // Required: A PSR 6 cache service

                // Cache Config
                'namespace' => '', // Optional : a string prefixed to the keys of the items.
                'maxLifetime' => 0, // Optional : The max lifetime of items propagated from lower adapters to upper ones
            ],
        ],
    ],
];
```

Docs: [Proxy Cache Adapter](https://symfony.com/doc/current/components/cache/adapters/proxy_adapter.html)


#### Redis
This adapter stores the values in-memory using one (or more) Redis server instances.

Unlike the APCu adapter, and similarly to the Memcached adapter, it is not limited to the current server’s shared
memory; you can store contents independent of your PHP environment. The ability to utilize a cluster of servers to
provide redundancy and/or fail-over is also available.

```php
<?php

return [
    'symfonyCache' => [
        'default' => [  
            'type' => 'Redis',
            'options' => [
                // Connection config
                // A client service, or dsn(s) is required.  Default: localhost
                'client' => 'service-name', // Redis service name.  Will be pulled from the container.
                'dsn' => 'redis://[pass@][ip|host|socket[:port]][/db-index]', // Dsn for connections.

                // Connection Options.  Not needed if using a service.
                'class' => '\Redis', // Optional.  Specifies the connection library to return, either \Redis or \Predis\Client. Default: \Redis
                'compression' => true, // Optional.  Enables or disables compression of items. Default: true
                'lazy' => false, // Optional.  Enables or disables lazy connections to the backend. Default: false
                'persistent' => 0, // Optional.  Enables or disables use of persistent connections. Default: 0
                'persistent_id' => 'some-id', // Optional.  Specifies the persistent id string to use for a persistent connection. Default: null
                'read_timeout' => 0, // Optional.  Specifies the read timeout. Default: 0
                'retry_interval' => 0, // Optional.  Specifies the delay (in milliseconds) between reconnection attempts. Default: 0
                'tcp_keepalive' => 0, // Optional.  Specifies the TCP-keepalive timeout (in seconds) of the connection. Default: 0
                'timeout' => 30, // Optional.  Specifies the timeout (in seconds) used to connect to a Redis server. Default: 30
                
                // Cache Config
                'namespace' => '', // Optional : a string prefixed to the keys of the items.
                'maxLifetime' => 0, // Optional : The max lifetime of items propagated from lower adapters to upper ones
            ],
        ],
    ],
];
```

Docs: [Redis Cache Adapter](https://symfony.com/doc/current/components/cache/adapters/redis_adapter.html)


These docs, including the code samples, are licensed under a [Creative Commons BY-SA 3.0](https://creativecommons.org/licenses/by-sa/3.0/) license. 
