<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager\SQLite;

use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\Manager\AbstractTableManagerExtraTestCase;

class SQLiteTableManagerExtraTest extends AbstractTableManagerExtraTestCase
{
    protected static DbPlatform $platform = DbPlatform::SQLite;

    protected static string $driver = 'pdo_sqlite';

    protected static bool $supportsColumnAlter = false;

    protected static bool $supportsAddConstraint = false;

    protected static function dropTable(string $table): void
    {
        self::$baseConn->exec("DROP TABLE IF EXISTS $table");
    }
}
