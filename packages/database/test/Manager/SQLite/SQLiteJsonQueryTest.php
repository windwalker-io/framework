<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager\SQLite;

use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\Manager\AbstractJsonQueryTestCase;

class SQLiteJsonQueryTest extends AbstractJsonQueryTestCase
{
    protected static DbPlatform $platform = DbPlatform::SQLite;

    protected static string $driver = 'pdo_sqlite';

    protected static function createJsonTable(): void
    {
        self::$baseConn->exec('DROP TABLE IF EXISTS ww_json_items');
        self::$baseConn->exec('CREATE TABLE ww_json_items (id INTEGER PRIMARY KEY, data TEXT)');
    }
}
