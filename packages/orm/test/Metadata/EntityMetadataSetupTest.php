<?php

declare(strict_types=1);

namespace Windwalker\ORM\Test\Metadata;

use PHPUnit\Framework\Attributes\Test;
use Windwalker\ORM\Attributes\Column;
use Windwalker\ORM\Event\EntitySetupEvent;
use Windwalker\ORM\ORM;
use Windwalker\ORM\Test\AbstractORMTestCase;
use Windwalker\ORM\Test\Entity\StubFlower;
use Windwalker\ORM\Test\Entity\StubRose;

/**
 * Regression tests for entity metadata being requested while another entity is being set up.
 */
class EntityMetadataSetupTest extends AbstractORMTestCase
{
    #[Test]
    public function metadataRequestedDuringSetupIsSameInstanceWithFullColumns(): void
    {
        $orm = new ORM(self::$db);
        $seen = [];

        $orm->on(
            EntitySetupEvent::class,
            function (EntitySetupEvent $event) use ($orm, &$seen) {
                if ($event->metadata->getClassName() === StubFlower::class) {
                    $orm->getEntityMetadata(StubRose::class);
                    $seen[] = $orm->getEntityMetadata(StubFlower::class);
                }
            }
        );

        $metadata = $orm->getEntityMetadata(StubFlower::class);

        self::assertCount(1, $seen);
        self::assertSame($metadata, $seen[0]);
        self::assertSame($metadata, $orm->getEntityMetadata(StubFlower::class));
        self::assertArrayHasKey('params', $metadata->getColumns());
    }

    #[Test]
    public function columnsCacheIsRefreshedWhenMemberAdded(): void
    {
        $orm = new ORM(self::$db);
        $metadata = $orm->getEntityMetadata(StubFlower::class);

        $cols = $metadata->getColumns();
        $metadata->getPureColumns();

        $metadata->addMember(new \ReflectionProperty(StubFlower::class, 'c'), new Column('extra_c'));

        self::assertCount(count($cols) + 1, $metadata->getColumns());
        self::assertArrayHasKey('extra_c', $metadata->getPureColumns());
    }

    protected static function setupDatabase(): void
    {
        self::importFromFile(__DIR__ . '/../Stub/data.sql');
    }
}
