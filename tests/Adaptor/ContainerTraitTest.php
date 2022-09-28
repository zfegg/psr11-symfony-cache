<?php

declare(strict_types=1);

namespace Zfegg\Psr11SymfonyCache\Test\Adaptor;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Zfegg\Psr11SymfonyCache\Adapter\ContainerTrait;
use Zfegg\Psr11SymfonyCache\Adapter\MemcachedAdapterFactory;

/**
 * @covers \Zfegg\Psr11SymfonyCache\Adapter\ContainerTrait
 */
class ContainerTraitTest extends TestCase
{
    /** @var MockObject|MemcachedAdapterFactory */
    protected $trait;

    /** @var MockObject|ContainerInterface */
    protected $mockContainer;

    /** @psalm-suppress all */
    protected function setUp(): void
    {
        $this->mockContainer = $this->createMock(ContainerInterface::class);
        $this->trait = $this->getMockForTrait(ContainerTrait::class);

        $this->assertTrue(method_exists($this->trait, 'getContainer'));
        $this->assertTrue(method_exists($this->trait, 'setContainer'));
    }

    public function testConstructor(): void
    {
    }

    /** @psalm-suppress all */
    public function testGetAndSetContainer(): void
    {
        $this->trait->setContainer($this->mockContainer);
        $result = $this->trait->getContainer();
        $this->assertEquals($result, $this->mockContainer);
    }
}
