<?php
declare(strict_types=1);

namespace Tests;

use
    Fyre\Entity\Entity,
    PHPUnit\Framework\TestCase;

final class EntityTest extends TestCase
{

    use
        AccessTest,
        DirtyTest,
        ErrorTest,
        FieldTest,
        HiddenTest,
        InvalidTest,
        MagicTest,
        MutationTest,
        OriginalTest,
        VirtualTest;

    public function testEntityData(): void
    {
        $entity = new Entity([
            'a' => 1
        ]);

        $this->assertSame(
            1,
            $entity->get('a')
        );

        $this->assertNull(
            $entity->getSource()
        );

        $this->assertTrue(
            $entity->isNew()
        );

        $this->assertFalse(
            $entity->isDirty()
        );
    }

    public function testEntitySource(): void
    {
        $entity = new Entity([], ['source' => 'test']);

        $this->assertSame(
            'test',
            $entity->getSource()
        );
    }

    public function testEntityNotNew(): void
    {
        $entity = new Entity([], ['new' => false]);

        $this->assertFalse(
            $entity->isNew()
        );
    }

    public function testEntityNotClean(): void
    {
        $entity = new Entity(['a' => 1], ['clean' => false]);

        $this->assertTrue(
            $entity->isDirty()
        );
    }

    public function testEntitySetSource(): void
    {
        $entity = new Entity();

        $this->assertSame(
            $entity,
            $entity->setSource('test')
        );

        $this->assertSame(
            'test',
            $entity->getSource()
        );
    }

    public function testEntitySetNew(): void
    {
        $entity = new Entity();

        $this->assertSame(
            $entity,
            $entity->setNew(false)
        );

        $this->assertFalse(
            $entity->isNew()
        );
    }

    public function testClean(): void
    {
        $entity = new Entity();

        $this->assertSame(
            $entity,
            $entity->clean()
        );
    }

}
