<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager;

use PHPUnit\Framework\Attributes\Test;
use Windwalker\Database\Test\AbstractDatabaseTestCase;

/**
 * Platform-agnostic DatabaseManager / SchemaManager (table listing) tests.
 *
 * SQLite uses this directly: its "database" is a file, so create/drop is covered by SQLitePlatformTest (ATTACH).
 */
abstract class AbstractDatabaseManagerTestCase extends AbstractDatabaseTestCase
{
    /**
     * Should (re)create `ww_mgr_items(id INT NOT NULL)`.
     */
    abstract protected static function createItemsTable(): void;

    abstract protected static function defaultSchemaName(): string;

    protected static function setupDatabase(): void
    {
        static::createItemsTable();
    }

    #[Test]
    public function databaseExists(): void
    {
        $dbName = static::$db->getPlatform()->getCurrentDatabase();

        self::assertTrue(static::$db->getDatabase($dbName)->exists());
        self::assertFalse(static::$db->getDatabase('ww_not_exists_db')->exists());
    }

    #[Test]
    public function schemaListsTablesAndViews(): void
    {
        $schema = static::$db->getSchema(static::defaultSchemaName(), true);

        self::assertTrue($schema->exists());
        self::assertArrayHasKey('ww_mgr_items', $schema->getTables());
        self::assertSame([], array_diff(array_keys($schema->getViews()), array_keys($schema->getTables(true))));
        self::assertSame('ww_mgr_items', $schema->getTableDetail('ww_mgr_items')->tableName);
    }

    #[Test]
    public function schemaHasTable(): void
    {
        $schema = static::$db->getSchema(static::defaultSchemaName(), true);

        self::assertTrue($schema->hasTable('ww_mgr_items'));
        self::assertTrue($schema->hasTable('#__mgr_items'));
        self::assertFalse($schema->hasTable('ww_not_exists_table'));
    }

    #[Test]
    public function schemaGetTableIsCachedUnlessNew(): void
    {
        $schema = static::$db->getSchema(static::defaultSchemaName(), true);

        self::assertSame($schema->getTable('ww_mgr_items'), $schema->getTable('ww_mgr_items'));
        self::assertNotSame($schema->getTable('ww_mgr_items'), $schema->getTable('ww_mgr_items', true));
    }
}
