<?php

declare(strict_types=1);

namespace Zfegg\Psr11SymfonyCache\Adapter;

use Psr\Container\ContainerInterface;

interface ContainerAwareInterface
{
    public function getContainer(): ContainerInterface;
    public function setContainer(ContainerInterface $container): void;
}
