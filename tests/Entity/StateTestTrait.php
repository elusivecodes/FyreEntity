<?php
declare(strict_types=1);

namespace Tests\Entity;

use Fyre\Entity\Entity;

trait StateTestTrait
{
    public function testState(): void
    {
        $entity = new Entity([
            'test' => 0
        ]);

        $entity->set('test', 1);
        $entity->setError('test', 'error');
        $entity->setInvalid('test', '');

        $this->assertSame(
            $entity,
            $entity->saveState()
        );

        $entity->setNew(false);
        $entity->set('test', 2);
        $entity->unset('test');
        $entity->set('other', true);

        $this->assertSame(
            $entity,
            $entity->restoreState()
        );

        $this->assertTrue(
            $entity->isNew()
        );

        $this->assertSame(
            1,
            $entity->get('test')
        );

        $this->assertSame(
            0,
            $entity->getOriginal('test')
        );

        $this->assertSame(
            ['error'],
            $entity->getError('test')
        );

        $this->assertSame(
            '',
            $entity->getInvalid('test')
        );

        $this->assertNull(
            $entity->get('other')
        );
    }
}
