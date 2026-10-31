<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager\PostgreSQL;

use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\Manager\AbstractTableManagerExtraTestCase;

class PostgreSQLTableManagerExtraTest extends AbstractTableManagerExtraTestCase
{
    protected static DbPlatform $platform = DbPlatform::PostgreSQL;

    protected static string $driver = 'pdo_pgsql';

    protected static string $schema = 'public';

    protected static function dropTable(string $table): void
    {
        self::$baseConn->exec("DROP TABLE IF EXISTS $table");
    }
}
