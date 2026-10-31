<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager\PostgreSQL;

use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\Manager\AbstractWriterManagerTestCase;

class PostgreSQLWriterManagerCrudTest extends AbstractWriterManagerTestCase
{
    protected static DbPlatform $platform = DbPlatform::PostgreSQL;

    protected static string $driver = 'pdo_pgsql';

    protected static string $schema = 'public';

    protected static function createWriterTable(): void
    {
        self::$baseConn->exec('DROP TABLE IF EXISTS writer_items');
        self::$baseConn->exec(
            'CREATE TABLE writer_items (id SERIAL PRIMARY KEY, title VARCHAR(255) NOT NULL DEFAULT \'\' UNIQUE, '
            . 'state INTEGER NULL DEFAULT 0, params TEXT NULL)'
        );
    }

    protected static function resetWriterTableData(): void
    {
        self::$baseConn->exec('TRUNCATE TABLE writer_items RESTART IDENTITY');
    }
}
