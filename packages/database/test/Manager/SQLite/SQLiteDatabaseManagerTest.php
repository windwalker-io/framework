<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager\SQLite;

use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\Manager\AbstractDatabaseManagerTestCase;

class SQLiteDatabaseManagerTest extends AbstractDatabaseManagerTestCase
{
    protected static DbPlatform $platform = DbPlatform::SQLite;

    protected static string $driver = 'pdo_sqlite';

    protected static function createItemsTable(): void
    {
        self::$baseConn->exec('DROP TABLE IF EXISTS ww_mgr_items');
        self::$baseConn->exec('CREATE TABLE ww_mgr_items (id INT NOT NULL)');
    }

    protected static function defaultSchemaName(): string
    {
        return 'main';
    }
}
