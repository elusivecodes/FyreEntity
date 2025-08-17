<?php
declare(strict_types=1);

namespace Tests;

use Fyre\Entity\Entity;
use Fyre\Entity\EntityLocator;
use Fyre\Utility\Inflector;
use Fyre\Utility\Traits\MacroTrait;
use PHPUnit\Framework\TestCase;
use Tests\Mock\MockEntity;

use function class_uses;

final class EntityLocatorTest extends TestCase
{
    protected EntityLocator $locator;

    public function testFind(): void
    {
        $this->assertSame(
            MockEntity::class,
            $this->locator->find('MockEntity')
        );
    }

    public function testFindAlias(): void
    {
        $this->assertSame(
            'MockEntities',
            $this->locator->findAlias(MockEntity::class)
        );
    }

    public function testFindInvalid(): void
    {
        $this->assertSame(
            Entity::class,
            $this->locator->find('Invalid')
        );
    }

    public function testFindPlural(): void
    {
        $this->assertSame(
            MockEntity::class,
            $this->locator->find('MockEntities')
        );
    }

    public function testGetDefaultEntityClass(): void
    {
        $this->assertSame(
            Entity::class,
            $this->locator->getDefaultEntityClass()
        );
    }

    public function testGetNamespaces(): void
    {
        $this->assertSame(
            [
                'Tests\Mock\\',
            ],
            $this->locator->getNamespaces()
        );
    }

    public function testHasNamespace(): void
    {
        $this->assertTrue(
            $this->locator->hasNamespace('Tests\Mock')
        );
    }

    public function testHasNamespaceInvalid(): void
    {
        $this->assertFalse(
            $this->locator->hasNamespace('Tests\Invalid')
        );
    }

    public function testMacroable(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(EntityLocator::class)
        );
    }

    public function testMap(): void
    {
        $this->assertSame(
            $this->locator,
            $this->locator->map('Test', MockEntity::class)
        );

        $this->assertSame(
            MockEntity::class,
            $this->locator->find('Test')
        );

        $this->assertSame(
            'Test',
            $this->locator->findAlias(MockEntity::class)
        );
    }

    public function testRemoveNamespace(): void
    {
        $this->assertSame(
            $this->locator,
            $this->locator->removeNamespace('Tests\Mock')
        );

        $this->assertFalse(
            $this->locator->hasNamespace('Tests\Mock')
        );
    }

    public function testRemoveNamespaceInvalid(): void
    {
        $this->assertSame(
            $this->locator,
            $this->locator->removeNamespace('Tests\Invalid')
        );
    }

    protected function setUp(): void
    {
        $inflector = new Inflector();

        $this->locator = new EntityLocator($inflector);
        $this->locator->addNamespace('Tests\Mock');
    }
}
