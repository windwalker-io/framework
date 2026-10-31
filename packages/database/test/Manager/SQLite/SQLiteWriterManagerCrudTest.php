<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager\SQLite;

use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\Manager\AbstractWriterManagerTestCase;

class SQLiteWriterManagerCrudTest extends AbstractWriterManagerTestCase
{
    protected static DbPlatform $platform = DbPlatform::SQLite;

    protected static string $driver = 'pdo_sqlite';

    protected static function createWriterTable(): void
    {
        self::$baseConn->exec('DROP TABLE IF EXISTS writer_items');
        self::$baseConn->exec(
            'CREATE TABLE writer_items (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT NOT NULL DEFAULT \'\' UNIQUE, '
            . 'state INTEGER NULL DEFAULT 0, params TEXT NULL)'
        );
    }

    protected static function resetWriterTableData(): void
    {
        self::$baseConn->exec('DELETE FROM writer_items');
        self::$baseConn->exec("DELETE FROM sqlite_sequence WHERE name = 'writer_items'");
    }
}
