<?php
declare(strict_types=1);

namespace Tests;

use Fyre\Entity\Entity;
use Fyre\Entity\EntityLocator;
use PHPUnit\Framework\TestCase;

final class EntityLocatorTest extends TestCase
{
    public function testFind(): void
    {
        $this->assertSame(
            '\Tests\Mock\MockEntity',
            EntityLocator::find('MockEntity')
        );
    }

    public function testFindInvalid(): void
    {
        $this->assertSame(
            'Fyre\Entity\Entity',
            EntityLocator::find('Invalid')
        );
    }

    public function testFindPlural(): void
    {
        $this->assertSame(
            '\Tests\Mock\MockEntity',
            EntityLocator::find('MockEntities')
        );
    }

    public function testGetDefaultEntityClass(): void
    {
        $this->assertSame(
            Entity::class,
            EntityLocator::getDefaultEntityClass()
        );
    }

    public function testGetNamespaces(): void
    {
        $this->assertSame(
            [
                '\Tests\Mock\\',
            ],
            EntityLocator::getNamespaces()
        );
    }

    public function testHasNamespace(): void
    {
        $this->assertTrue(
            EntityLocator::hasNamespace('Tests\Mock')
        );
    }

    public function testHasNamespaceInvalid(): void
    {
        $this->assertFalse(
            EntityLocator::hasNamespace('Tests\Invalid')
        );
    }

    public function testRemoveNamespace(): void
    {
        $this->assertTrue(
            EntityLocator::removeNamespace('Tests\Mock')
        );

        $this->assertFalse(
            EntityLocator::hasNamespace('Tests\Mock')
        );
    }

    public function testRemoveNamespaceInvalid(): void
    {
        $this->assertFalse(
            EntityLocator::removeNamespace('Tests\Invalid')
        );
    }

    public static function setUpBeforeClass(): void
    {
        EntityLocator::clear();
        EntityLocator::addNamespace('Tests\Mock');
        EntityLocator::setDefaultEntityClass(Entity::class);
    }
}
