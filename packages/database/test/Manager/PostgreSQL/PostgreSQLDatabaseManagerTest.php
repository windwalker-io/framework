<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager\PostgreSQL;

use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\Manager\AbstractCreatableDatabaseManagerTestCase;

class PostgreSQLDatabaseManagerTest extends AbstractCreatableDatabaseManagerTestCase
{
    protected static DbPlatform $platform = DbPlatform::PostgreSQL;

    protected static string $driver = 'pdo_pgsql';

    protected static string $schema = 'public';

    protected static function createItemsTable(): void
    {
        self::$baseConn->exec('DROP TABLE IF EXISTS ww_mgr_items');
        self::$baseConn->exec('CREATE TABLE ww_mgr_items (id INT NOT NULL)');
    }

    protected static function dropDatabaseIfExists(string $name): void
    {
        self::$baseConn->exec("DROP DATABASE IF EXISTS $name");
    }

    protected static function defaultSchemaName(): string
    {
        return 'public';
    }
}
