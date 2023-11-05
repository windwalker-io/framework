<?php

declare(strict_types=1);

namespace Windwalker\ORM\Test\Relation\Strategy;

use Windwalker\ORM\Relation\Action;
use Windwalker\ORM\Test\AbstractORMTestCase;
use Windwalker\ORM\Test\Entity\StubLocation;
use Windwalker\ORM\Test\Entity\StubSakura;

/**
 * The ManyToOneTest class.
 */
class ManyToOneTest extends AbstractORMTestCase
{
    public function testLoad()
    {
        $mapper = $this->createTestMapper();

        /** @var StubSakura $sakura */
        $sakura = $mapper->findOne(1);

        $location = $sakura->getLocation();

        self::assertEquals(
            $sakura->getLocationNo(),
            $location->getNo()
        );

        self::assertEquals(
            $sakura->getLocationNo(),
            $sakura->getLoc()->getNo()
        );
    }

    public function createTestMapper(
        string $onUpdate = Action::CASCADE,
        string $onDelete = Action::CASCADE,
        bool $flush = false
    ) {
        $mapper = self::$orm->mapper(StubSakura::class);

        $mapper->getMetadata()
            ->getRelationManager()
            ->manyToOne('loc')
            ->targetTo(StubLocation::class, location_no: 'no')
            ->onUpdate($onUpdate)
            ->onDelete($onDelete);

        return $mapper;
    }

    /**
     * @inheritDoc
     */
    protected static function setupDatabase(): void
    {
        self::importFromFile(__DIR__ . '/../../Stub/relations.sql');
    }
}
