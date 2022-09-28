<?php

declare(strict_types=1);

namespace Zfegg\Psr11SymfonyCache;

use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Zfegg\Psr11SymfonyCache\Adapter\APCuAdapterFactory;
use Zfegg\Psr11SymfonyCache\Adapter\ArrayAdapterFactory;
use Zfegg\Psr11SymfonyCache\Adapter\ChainAdapterFactory;
use Zfegg\Psr11SymfonyCache\Adapter\ContainerAwareInterface;
use Zfegg\Psr11SymfonyCache\Adapter\CouchbaseAdapterFactory;
use Zfegg\Psr11SymfonyCache\Adapter\FactoryInterface;
use Zfegg\Psr11SymfonyCache\Adapter\FilesystemAdapterFactory;
use Zfegg\Psr11SymfonyCache\Adapter\MemcachedAdapterFactory;
use Zfegg\Psr11SymfonyCache\Adapter\PdoAdapterFactory;
use Zfegg\Psr11SymfonyCache\Adapter\PhpArrayAdapterFactory;
use Zfegg\Psr11SymfonyCache\Adapter\PhpFilesAdapterFactory;
use Zfegg\Psr11SymfonyCache\Adapter\ProxyAdapterFactory;
use Zfegg\Psr11SymfonyCache\Adapter\RedisAdapterFactory;
use Zfegg\Psr11SymfonyCache\Exception\InvalidConfigException;
use Zfegg\Psr11SymfonyCache\Exception\InvalidContainerException;
use Zfegg\Psr11SymfonyCache\Exception\MissingConfigException;

/** @SuppressWarnings(PHPMD.CouplingBetweenObjects) */
class CacheFactory
{
    protected $configKey = 'default';

    /** @psalm-suppress MissingParamType */
    public static function __callStatic($name, $arguments): AdapterInterface
    {
        if (
            empty($arguments[0])
            || !$arguments[0] instanceof ContainerInterface
        ) {
            throw new InvalidContainerException(
                'Argument 0 must be an instance of a PSR-11 container'
            );
        }

        $factory = new self($name);
        return $factory($arguments[0]);
    }

    public function __construct(string $configKey = 'default')
    {
        $this->configKey = $configKey;
    }

    public function __invoke(ContainerInterface $container): AdapterInterface
    {
        $config = $this->getConfig($container);
        return $this->get($container, $config['type'], $config['options'] ?? []);
    }

    /** @SuppressWarnings(PHPMD.CyclomaticComplexity) */
    private function getFactoryClassName(string $type): ?string
    {
        if (
            strtolower($type) !== 'pdo'
            && strtolower($type) !== 'memcached'
            && strtolower($type) !== 'redis'
            && class_exists($type)
        ) {
            return $type;
        }

        switch (strtolower($type)) {
            case 'apcu':
                return APCuAdapterFactory::class;
            case 'array':
                return ArrayAdapterFactory::class;
            case 'chain':
                return ChainAdapterFactory::class;
            case 'couchbase':
                return CouchbaseAdapterFactory::class;
            case 'filesystem':
                return FilesystemAdapterFactory::class;
            case 'memcached':
                return MemcachedAdapterFactory::class;
            case 'pdo':
                return PdoAdapterFactory::class;
            case 'phparray':
                return PhpArrayAdapterFactory::class;
            case 'phpfiles':
                return PhpFilesAdapterFactory::class;
            case 'proxy':
                return ProxyAdapterFactory::class;
            case 'redis':
                return RedisAdapterFactory::class;
        }

        return null;
    }

    private function get(ContainerInterface $container, string $type, array $options = []): AdapterInterface
    {
        $className = $this->getFactoryClassName($type);

        if (!$className) {
            throw new InvalidConfigException(
                'Unable to locate a factory by the name of: ' . $type
            );
        }

        if (!in_array(FactoryInterface::class, class_implements($className))) {
            throw new InvalidConfigException(
                'Class ' . $className . ' must be an instance of ' . FactoryInterface::class
            );
        }

        /** @var FactoryInterface $factory */
        $factory = new $className();

        if ($factory instanceof ContainerAwareInterface) {
            $factory->setContainer($container);
        }

        // @codeCoverageIgnoreStart
        // Unreachable code in tests
        if (!is_callable($factory)) {
            throw new InvalidConfigException(
                'Class ' . $className . ' must be callable.'
            );
        }
        // @codeCoverageIgnoreEnd

        return $factory($options);
    }

    private function getConfig(ContainerInterface $container): array
    {
        $config = $this->getConfigArray($container);

        if (empty($config['cache'][$this->configKey])) {
            throw new InvalidConfigException(
                "No config found for adapter: " . $this->configKey
            );
        }

        return $config['cache'][$this->configKey];
    }

    private function getConfigArray(ContainerInterface $container): array
    {
        // Symfony config is parameters. //
        if (
            method_exists($container, 'getParameter')
            && method_exists($container, 'hasParameter')
            && $container->hasParameter('cache')
        ) {
            return ['cache' => $container->getParameter('cache')];
        }

        // Zend uses config key
        if ($container->has('config')) {
            return $container->get('config');
        }

        // Slim Config comes from "settings"
        if ($container->has('settings')) {
            return ['cache' => $container->get('settings')['cache']];
        }

        throw new MissingConfigException("Unable to locate Cache configuration");
    }
}
