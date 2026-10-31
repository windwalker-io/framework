<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager\SQLServer;

use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\Manager\AbstractWriterManagerTestCase;

class SQLServerWriterManagerCrudTest extends AbstractWriterManagerTestCase
{
    protected static DbPlatform $platform = DbPlatform::SQLServer;

    protected static string $driver = 'pdo_sqlsrv';

    protected static function createWriterTable(): void
    {
        self::$baseConn->exec("IF OBJECT_ID('writer_items', 'U') IS NOT NULL DROP TABLE writer_items");
        self::$baseConn->exec(
            'CREATE TABLE writer_items (id INT IDENTITY(1,1) PRIMARY KEY, title NVARCHAR(255) NOT NULL UNIQUE '
            . 'DEFAULT \'\', state INT NULL DEFAULT 0, params NVARCHAR(MAX) NULL)'
        );
    }

    protected static function resetWriterTableData(): void
    {
        self::$baseConn->exec('TRUNCATE TABLE writer_items');
    }
}
