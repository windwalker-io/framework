<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Platform;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\AbstractDatabaseTestCase;

class PostgreSQLPlatformExtraTest extends AbstractDatabaseTestCase
{
    protected static DbPlatform $platform = DbPlatform::PostgreSQL;

    protected static string $driver = 'pdo_pgsql';

    protected static string $schema = 'public';

    protected static function setupDatabase(): void {}

    #[Before]
    protected function createTable(): void
    {
        self::$baseConn->exec('DROP TABLE IF EXISTS ww_pg_extra');
        self::$baseConn->exec('CREATE TABLE ww_pg_extra (id SERIAL PRIMARY KEY, title VARCHAR(50))');
        self::$baseConn->exec('CREATE INDEX ww_pg_extra_title ON ww_pg_extra (title)');
    }

    #[After]
    protected function dropTable(): void
    {
        self::$baseConn->exec('DROP TABLE IF EXISTS ww_pg_extra');
    }

    #[Test]
    public function setColumnCommentWritesComment(): void
    {
        self::$db->getPlatform()->setColumnComment('ww_pg_extra', 'title', 'Hello comment', 'public');

        $comment = self::$baseConn->query(
            "SELECT col_description('public.ww_pg_extra'::regclass, 2)"
        )->fetchColumn();

        self::assertSame('Hello comment', $comment);
    }

    #[Test]
    public function dropIndexRemovesIndex(): void
    {
        self::$db->getPlatform()->dropIndex('ww_pg_extra', 'ww_pg_extra_title', 'public');

        $count = self::$baseConn->query(
            "SELECT COUNT(*) FROM pg_indexes WHERE indexname = 'ww_pg_extra_title'"
        )->fetchColumn();

        self::assertSame(0, (int) $count);
    }

    #[Test]
    public function lastInsertIdFromSequenceName(): void
    {
        self::$db->execute("INSERT INTO ww_pg_extra (title) VALUES ('a')");

        self::assertSame(
            '1',
            self::$db->getPlatform()->lastInsertId('', 'ww_pg_extra_id_seq')
        );
    }

    #[Test]
    public function lastInsertIdFromQueryText(): void
    {
        self::$db->execute("INSERT INTO ww_pg_extra (title) VALUES ('a')");
        $id = self::$db->getPlatform()->lastInsertId('INSERT INTO "ww_pg_extra" (title) VALUES (1)');

        self::assertNotNull($id);
        self::assertNull(self::$db->getPlatform()->lastInsertId('UPDATE foo SET a = 1'));
    }
}
