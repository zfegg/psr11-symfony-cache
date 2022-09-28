<?php

declare(strict_types=1);

namespace Zfegg\Psr11SymfonyCache\Adapter;

use Symfony\Component\Cache\Adapter\AdapterInterface;

interface FactoryInterface
{
    public function __invoke(array $options): AdapterInterface;
}
