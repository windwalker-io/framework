<?php

declare(strict_types=1);

namespace Windwalker\ORM\Test\Upsert;

use PHPUnit\Framework\Attributes\Test;
use Windwalker\Database\Schema\Schema;
use Windwalker\ORM\Test\AbstractORMTestCase;
use Windwalker\ORM\Test\Entity\StubUpsertFlower;

abstract class AbstractOrmUpsertTestCase extends AbstractORMTestCase
{
    protected static function setupDatabase(): void {}

    #[Test]
    public function upsertInsertsAndUpdatesEntity(): void
    {
        $db = self::$db;
        $tm = $db->getTableManager('ww_tmp_upsert_flower');
        $tm->drop(true);
        $sqlite = $db->getPlatform()->getName() === 'SQLite';
        $tm->create(function (Schema $s) use ($sqlite) {
            $sqlite ? $s->primary('id') : $s->primaryBigint('id');
            $s->varchar('title');
            $s->integer('state')->defaultValue(0);
        });

        $mapper = self::$orm->mapper(StubUpsertFlower::class);

        $a = new StubUpsertFlower();
        $a->title = 'A';
        $a = $mapper->upsert($a);
        self::assertNotEmpty($a->id, 'insert returns id');

        $a->title = 'A2';
        $a->state = 5;
        $b = $mapper->upsert($a);
        self::assertSame($a->id, $b->id);
        $row = $mapper->findOne($a->id);
        self::assertSame('A2', $row->title);
        self::assertSame(5, $row->state);
        self::assertSame(1, (int) $db->select('*')->from('ww_tmp_upsert_flower')->count());

        $c = $mapper->upsert(['title' => 'C', 'state' => 1]);
        self::assertNotEmpty($c->id);
        self::assertNotSame($a->id, $c->id);

        $d = $mapper->upsert(['id' => $c->id, 'title' => 'C2', 'state' => 2], null, ['title']);
        self::assertSame('C2', $mapper->findOne($c->id)->title);
        self::assertSame(1, $mapper->findOne($c->id)->state);

        $tm->drop(true);
    }
}
