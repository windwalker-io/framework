<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager\PostgreSQL;

use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\Manager\AbstractUpsertTestCase;

class PostgreSQLWriterManagerTest extends AbstractUpsertTestCase
{
    protected static DbPlatform $platform = DbPlatform::PostgreSQL;

    protected static string $driver = 'pdo_pgsql';

    protected static string $schema = 'public';

    protected static function createUpsertTable(): void
    {
        self::$baseConn->exec('DROP TABLE IF EXISTS ww_upsert_items');
        self::$baseConn->exec(
            'CREATE TABLE ww_upsert_items '
            . '(id SERIAL PRIMARY KEY, title VARCHAR(100) NOT NULL UNIQUE, state INTEGER)'
        );
    }
}
