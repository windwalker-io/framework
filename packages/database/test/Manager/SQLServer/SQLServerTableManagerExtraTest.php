<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager\SQLServer;

use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\Manager\AbstractTableManagerExtraTestCase;

class SQLServerTableManagerExtraTest extends AbstractTableManagerExtraTestCase
{
    protected static DbPlatform $platform = DbPlatform::SQLServer;

    protected static string $driver = 'pdo_sqlsrv';

    protected static function dropTable(string $table): void
    {
        self::$baseConn->exec("IF OBJECT_ID('$table', 'U') IS NOT NULL DROP TABLE $table");
    }
}
