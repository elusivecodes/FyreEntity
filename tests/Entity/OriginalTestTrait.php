<?php
declare(strict_types=1);

namespace Tests\Entity;

use Fyre\Entity\Entity;

trait OriginalTestTrait
{
    public function tesExtractOriginalFallback(): void
    {
        $entity = new Entity([
            'test' => 1,
        ]);

        $this->assertSame(
            [
                'test' => 1,
            ],
            $entity->extractOriginal('test')
        );
    }

    public function testCleanOriginal(): void
    {
        $entity = new Entity([
            'test' => 1,
        ]);

        $entity->set('test', 2);
        $entity->clean();

        $this->assertSame(
            2,
            $entity->getOriginal('test')
        );
    }

    public function testClearOriginal(): void
    {
        $entity = new Entity([
            'test' => 1,
        ]);

        $entity->set('test', 2);
        $entity->clear(['test']);

        $this->assertNull(
            $entity->getOriginal('test')
        );
    }

    public function testExtractOriginal(): void
    {
        $entity = new Entity([
            'test1' => 1,
            'test2' => 2,
            'test3' => 3,
        ]);

        $entity->set('test2', 4);

        $this->assertSame(
            [
                'test2' => 2,
                'test3' => 3,
            ],
            $entity->extractOriginal(['test2', 'test3'])
        );
    }

    public function testExtractOriginalInvalid(): void
    {
        $entity = new Entity();

        $this->assertSame(
            [
                'invalid' => null,
            ],
            $entity->extractOriginal(['invalid'])
        );
    }

    public function testGetOriginal(): void
    {
        $entity = new Entity([
            'test' => 1,
        ]);

        $entity->set('test', 2);

        $this->assertSame(
            1,
            $entity->getOriginal('test')
        );
    }

    public function testGetOriginalAfterUnset(): void
    {
        $entity = new Entity([
            'test' => 1,
        ]);

        $entity->set('test', 2);
        $entity->unset('test');

        $this->assertNull(
            $entity->getOriginal('test')
        );
    }

    public function testGetOriginalFallback(): void
    {
        $entity = new Entity([
            'test' => 1,
        ]);

        $this->assertSame(
            1,
            $entity->getOriginal('test')
        );
    }

    public function testGetOriginalFromSet(): void
    {
        $entity = new Entity();

        $entity->set('test', 1);
        $entity->set('test', 2);

        $this->assertSame(
            1,
            $entity->getOriginal('test')
        );
    }

    public function testGetOriginalInvalid(): void
    {
        $entity = new Entity();

        $this->assertNull(
            $entity->getOriginal('invalid')
        );
    }

    public function testGetOriginalMultipleSet(): void
    {
        $entity = new Entity([
            'test' => 1,
        ]);

        $entity->set('test', 2);
        $entity->set('test', 3);

        $this->assertSame(
            1,
            $entity->getOriginal('test')
        );
    }

    public function testUnsetOriginal(): void
    {
        $entity = new Entity([
            'test' => 1,
        ]);

        $entity->set('test', 2);
        $entity->unset('test');

        $this->assertNull(
            $entity->getOriginal('test')
        );
    }
}
