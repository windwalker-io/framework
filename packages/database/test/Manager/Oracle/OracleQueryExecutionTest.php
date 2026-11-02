<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager\Oracle;

use PHPUnit\Framework\Attributes\Test;
use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\AbstractDatabaseTestCase;

/**
 * Runs Oracle-specific Query / Grammar output against a real database.
 */
class OracleQueryExecutionTest extends AbstractDatabaseTestCase
{
    protected static DbPlatform $platform = DbPlatform::Oracle;

    protected static string $driver = 'pdo_oci';

    #[Test]
    public function cteAndSubqueries(): void
    {
        $ids = static fn(array $rows) => array_map('intval', array_column($rows, 'id'));

        $q = self::$db->select('id')
            ->with('c', self::$db->createQuery()->select('id')->from('ww_qx_a'))
            ->from('c')
            ->where('id', '>', 1)
            ->order('id');

        self::assertSame([2, 3], $ids($q->all()->dump()));

        $q = self::$db->select('t.id')
            ->from(self::$db->createQuery()->select('id')->from('ww_qx_a'), 't')
            ->where('t.id', 'in', self::$db->createQuery()->select('id')->from('ww_qx_a')->where('id', '<', 3))
            ->order('t.id');

        self::assertSame([1, 2], $ids($q->all()->dump()));
    }

    #[Test]
    public function unionWithOffsetFetch(): void
    {
        $query = self::$db->createQuery()
            ->union(self::$db->createQuery()->select('id')->from('ww_qx_a'))
            ->union(self::$db->createQuery()->select('id')->from('ww_qx_a'))
            ->order('id')
            ->limit(2)
            ->offset(1);

        self::assertSame([2, 3], array_map('intval', array_column(self::$db->prepare($query)->all()->dump(), 'id')));
    }

    #[Test]
    public function orderByRandomUsesDbmsRandom(): void
    {
        $query = self::$db->select('id')->from('ww_qx_a')->orderByRandom();

        self::assertStringContainsString('DBMS_RANDOM.VALUE', (string) $query->render());
        self::assertCount(3, $query->all()->dump());
    }

    #[Test]
    public function insertSelectAndForUpdate(): void
    {
        self::$db->execute(
            self::$db->createQuery()
                ->insert('ww_qx_b')
                ->columns('id', 'title')
                ->values(self::$db->createQuery()->select('id', 'title')->from('ww_qx_a')->where('id', '>', 1))
        );

        self::assertEquals(2, self::$db->select()->from('ww_qx_b')->count());

        $rows = self::$db->select('id')->from('ww_qx_b')->forUpdate('SKIP LOCKED')->all()->dump();

        self::assertCount(2, $rows);
    }

    #[Test]
    public function jsonConditions(): void
    {
        $count = static fn(string $method, mixed ...$args) => self::$db->select()
            ->from('ww_qx_json')
            ->$method(...$args)
            ->count();

        self::assertEquals(1, $count('whereJsonContainsAll', 'params -> foo ->> bar', 'yoo'));
        self::assertEquals(1, $count('whereJsonNotContainsAll', 'params -> foo ->> bar', 'yoo'));
        self::assertEquals(1, $count('whereJsonLength', 'params -> foo ->> bar', '>', 1));
    }

    protected static function setupDatabase(): void
    {
        self::$baseConn->exec('CREATE TABLE ww_qx_a (id NUMBER(10) NOT NULL, title VARCHAR2(50))');
        self::$baseConn->exec('CREATE TABLE ww_qx_b (id NUMBER(10) NOT NULL, title VARCHAR2(50))');
        self::$baseConn->exec("INSERT INTO ww_qx_a VALUES (1, 'a')");
        self::$baseConn->exec("INSERT INTO ww_qx_a VALUES (2, 'b')");
        self::$baseConn->exec("INSERT INTO ww_qx_a VALUES (3, 'c')");

        self::$baseConn->exec('CREATE TABLE ww_qx_json (id NUMBER(10) NOT NULL, params CLOB)');
        self::$baseConn->exec(
            'INSERT INTO ww_qx_json VALUES (1, \'{"foo":{"bar":["yoo","b"]}}\')'
        );
        self::$baseConn->exec(
            'INSERT INTO ww_qx_json VALUES (2, \'{"foo":{"bar":["z"]}}\')'
        );
    }
}
