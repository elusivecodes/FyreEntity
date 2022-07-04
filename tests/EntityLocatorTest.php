<?php
declare(strict_types=1);

namespace Tests;

use
    Fyre\Entity\EntityLocator,
    PHPUnit\Framework\TestCase;

final class EntityLocatorTest extends TestCase
{

    public function testFind(): void
    {
        $this->assertSame(
            '\Tests\Mock\MockEntity',
            EntityLocator::find('MockEntity')
        );
    }

    public function testFindPlural(): void
    {
        $this->assertSame(
            '\Tests\Mock\MockEntity',
            EntityLocator::find('MockEntities')
        );
    }

    public function testFindInvalid(): void
    {
        $this->assertSame(
            'Fyre\Entity\Entity',
            EntityLocator::find('Invalid')
        );
    }

    public static function setUpBeforeClass(): void
    {
        EntityLocator::clear();
        EntityLocator::addNamespace('Tests\Mock');
    }

}
