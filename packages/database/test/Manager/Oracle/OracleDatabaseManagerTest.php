<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager\Oracle;

use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\Manager\AbstractDatabaseManagerTestCase;

class OracleDatabaseManagerTest extends AbstractDatabaseManagerTestCase
{
    protected static DbPlatform $platform = DbPlatform::Oracle;

    protected static string $driver = 'pdo_oci';

    protected static function createItemsTable(): void
    {
        self::$baseConn->exec(
            "BEGIN EXECUTE IMMEDIATE 'DROP TABLE ww_mgr_items PURGE'; "
            . 'EXCEPTION WHEN OTHERS THEN IF SQLCODE != -942 THEN RAISE; END IF; END;'
        );
        self::$baseConn->exec('CREATE TABLE ww_mgr_items (id NUMBER(10) NOT NULL)');
    }

    protected static function defaultSchemaName(): string
    {
        return 'windwalker';
    }
}
