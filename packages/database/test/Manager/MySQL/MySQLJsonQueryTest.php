<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager\MySQL;

use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\Manager\AbstractJsonQueryTestCase;

class MySQLJsonQueryTest extends AbstractJsonQueryTestCase
{
    protected static DbPlatform $platform = DbPlatform::MySQL;

    protected static string $driver = 'pdo_mysql';

    protected static function createJsonTable(): void
    {
        self::$baseConn->exec('DROP TABLE IF EXISTS ww_json_items');
        self::$baseConn->exec('CREATE TABLE ww_json_items (id INT PRIMARY KEY, data JSON)');
    }
}
