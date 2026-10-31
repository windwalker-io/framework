<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager\MySQL;

use Windwalker\Database\Test\Manager\AbstractTableManagerExtraTestCase;

class MySQLTableManagerExtraTest extends AbstractTableManagerExtraTestCase
{
    protected static function dropTable(string $table): void
    {
        self::$baseConn->exec("DROP TABLE IF EXISTS $table");
    }
}
