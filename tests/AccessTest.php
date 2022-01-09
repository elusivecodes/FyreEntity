<?php
declare(strict_types=1);

namespace Tests;

use
    Fyre\Entity\Entity;

trait AccessTest
{

    public function testSetAccess(): void
    {
        $entity = new Entity();

        $this->assertSame(
            $entity,
            $entity->setAccess('test', true)
        );

        $this->assertTrue(
            $entity->isAccessible('test')
        );
    }

    public function testSetAccessFalse(): void
    {
        $entity = new Entity();

        $entity->setAccess('test', false);

        $this->assertFalse(
            $entity->isAccessible('test')
        );
    }

    public function testSetAccessAllOverwrites(): void
    {
        $entity = new Entity();

        $entity->setAccess('test', false);
        $entity->setAccess('*', true);

        $this->assertSame(
            ['*' => true],
            $entity->getAccessible()
        );
    }

    public function testIsAccessibleFallback(): void
    {
        $entity = new Entity();

        $this->assertTrue(
            $entity->isAccessible('test')
        );
    }

    public function testSetWithoutAccess(): void
    {
        $entity = new Entity([
            'test' => 1
        ]);

        $entity->setAccess('test', false);
        $entity->set('test', 2, ['guard' => true]);

        $this->assertSame(
            1,
            $entity->get('test')
        );
    }

    public function testSetWithoutGuard(): void
    {
        $entity = new Entity([
            'test' => 1
        ]);

        $entity->setAccess('test', false);
        $entity->set('test', 2);

        $this->assertSame(
            2,
            $entity->get('test')
        );
    }

    public function testFillWithoutAccess(): void
    {
        $entity = new Entity([
            'test' => 1
        ]);

        $entity->setAccess('test', false);
        $entity->fill([
            'test' => 2
        ]);

        $this->assertSame(
            1,
            $entity->get('test')
        );
    }

    public function testFillWithoutGuard(): void
    {
        $entity = new Entity([
            'test' => 1
        ]);

        $entity->setAccess('test', false);
        $entity->fill([
            'test' => 2
        ], ['guard' => false]);

        $this->assertSame(
            2,
            $entity->get('test')
        );
    }

}
