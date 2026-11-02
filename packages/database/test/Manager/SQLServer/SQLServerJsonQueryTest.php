<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager\SQLServer;

use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\Manager\AbstractJsonQueryTestCase;

class SQLServerJsonQueryTest extends AbstractJsonQueryTestCase
{
    protected static DbPlatform $platform = DbPlatform::SQLServer;

    protected static string $driver = 'pdo_sqlsrv';

    protected static function createJsonTable(): void
    {
        self::$baseConn->exec("IF OBJECT_ID('ww_json_items', 'U') IS NOT NULL DROP TABLE ww_json_items");
        self::$baseConn->exec('CREATE TABLE ww_json_items (id INT PRIMARY KEY, data NVARCHAR(MAX))');
    }
}
