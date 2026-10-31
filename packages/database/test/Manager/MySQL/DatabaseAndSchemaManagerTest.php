<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager\MySQL;

use Windwalker\Database\Test\Manager\AbstractCreatableDatabaseManagerTestCase;

class DatabaseAndSchemaManagerTest extends AbstractCreatableDatabaseManagerTestCase
{

    protected static function createItemsTable(): void
    {
        self::$baseConn->exec('CREATE TABLE IF NOT EXISTS `ww_mgr_items` (`id` int NOT NULL)');
    }

    protected static function dropDatabaseIfExists(string $name): void
    {
        self::$baseConn->exec("DROP DATABASE IF EXISTS $name");
    }

    protected static function defaultSchemaName(): string
    {
        return static::$db->getPlatform()->getCurrentDatabase();
    }
}
