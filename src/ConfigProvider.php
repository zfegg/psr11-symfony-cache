<?php

namespace Zfegg\Psr11SymfonyCache;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                'abstract_factories' => [
                    CacheServiceAbstractFactory::class,
                ]
            ]
        ];
    }
}
