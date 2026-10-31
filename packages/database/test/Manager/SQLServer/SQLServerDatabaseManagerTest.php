<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager\SQLServer;

use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\Manager\AbstractCreatableDatabaseManagerTestCase;

class SQLServerDatabaseManagerTest extends AbstractCreatableDatabaseManagerTestCase
{
    protected static DbPlatform $platform = DbPlatform::SQLServer;

    protected static string $driver = 'pdo_sqlsrv';

    protected static function createItemsTable(): void
    {
        self::$baseConn->exec("IF OBJECT_ID('ww_mgr_items', 'U') IS NOT NULL DROP TABLE ww_mgr_items");
        self::$baseConn->exec('CREATE TABLE ww_mgr_items (id INT NOT NULL)');
    }

    protected static function dropDatabaseIfExists(string $name): void
    {
        self::$baseConn->exec("DROP DATABASE IF EXISTS $name");
    }

    protected static function defaultSchemaName(): string
    {
        return 'dbo';
    }
}
