<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager\PostgreSQL;

use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\Manager\AbstractJsonQueryTestCase;

class PostgreSQLJsonQueryTest extends AbstractJsonQueryTestCase
{
    protected static DbPlatform $platform = DbPlatform::PostgreSQL;

    protected static string $driver = 'pdo_pgsql';

    protected static string $schema = 'public';

    protected static function createJsonTable(): void
    {
        self::$baseConn->exec('DROP TABLE IF EXISTS ww_json_items');
        self::$baseConn->exec('CREATE TABLE ww_json_items (id INT PRIMARY KEY, data JSONB)');
    }
}
