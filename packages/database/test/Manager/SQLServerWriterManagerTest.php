<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager;

use Windwalker\Database\Platform\DbPlatform;

class SQLServerWriterManagerTest extends AbstractUpsertTestCase
{
    protected static DbPlatform $platform = DbPlatform::SQLServer;

    protected static string $driver = 'pdo_sqlsrv';

    protected static function createUpsertTable(): void
    {
        self::$baseConn->exec("IF OBJECT_ID('ww_upsert_items', 'U') IS NOT NULL DROP TABLE ww_upsert_items");
        self::$baseConn->exec(
            'CREATE TABLE ww_upsert_items '
            . '(id INT IDENTITY(1,1) PRIMARY KEY, title NVARCHAR(100) NOT NULL UNIQUE, state INT NULL)'
        );
    }
}
