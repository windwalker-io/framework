<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager;

use PHPUnit\Framework\Attributes\Test;
use Windwalker\Database\Test\AbstractDatabaseTestCase;

class DatabaseAndSchemaManagerTest extends AbstractDatabaseTestCase
{
    protected static function setupDatabase(): void
    {
        self::$baseConn->exec('CREATE TABLE IF NOT EXISTS `ww_mgr_items` (`id` int NOT NULL)');
    }

    #[Test]
    public function databaseExists(): void
    {
        $dbName = static::$db->getPlatform()->getCurrentDatabase();

        self::assertTrue(static::$db->getDatabase($dbName)->exists());
        self::assertFalse(static::$db->getDatabase('ww_not_exists_db')->exists());
    }

    #[Test]
    public function databaseCreateAndDrop(): void
    {
        $manager = static::$db->getDatabase('ww_tmp_manager_db', true);

        try {
            $manager->create();
            self::assertTrue($manager->exists());

            // Existing DB with ifNotExists must not fail
            $manager->create(true);
            self::assertTrue($manager->exists());

            $manager->drop();
            self::assertFalse($manager->exists());
        } finally {
            static::$baseConn->exec('DROP DATABASE IF EXISTS ww_tmp_manager_db');
        }
    }

    #[Test]
    public function databaseDropIfExistsDropsExistingDatabase(): void
    {
        $manager = static::$db->getDatabase('ww_tmp_manager_db2', true);

        try {
            $manager->create();
            $manager->drop(true);

            self::assertFalse($manager->exists());
        } finally {
            static::$baseConn->exec('DROP DATABASE IF EXISTS ww_tmp_manager_db2');
        }
    }

    #[Test]
    public function schemaListsTablesAndViews(): void
    {
        $schema = static::$db->getSchema(static::$db->getPlatform()->getCurrentDatabase(), true);

        self::assertTrue($schema->exists());
        self::assertArrayHasKey('ww_mgr_items', $schema->getTables());
        self::assertSame([], array_diff(array_keys($schema->getViews()), array_keys($schema->getTables(true))));
        self::assertSame('ww_mgr_items', $schema->getTableDetail('ww_mgr_items')->tableName);
    }

    #[Test]
    public function schemaHasTable(): void
    {
        $schema = static::$db->getSchema(static::$db->getPlatform()->getCurrentDatabase(), true);

        self::assertTrue($schema->hasTable('ww_mgr_items'));
        self::assertTrue($schema->hasTable('#__mgr_items'));
        self::assertFalse($schema->hasTable('ww_not_exists_table'));
    }

    #[Test]
    public function schemaGetTableIsCachedUnlessNew(): void
    {
        $schema = static::$db->getSchema(static::$db->getPlatform()->getCurrentDatabase(), true);

        self::assertSame($schema->getTable('ww_mgr_items'), $schema->getTable('ww_mgr_items'));
        self::assertNotSame($schema->getTable('ww_mgr_items'), $schema->getTable('ww_mgr_items', true));
    }
}
