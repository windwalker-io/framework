<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager\SQLServer;

use PHPUnit\Framework\Attributes\Test;
use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\AbstractDatabaseTestCase;

class SQLServerSchemaManagerTest extends AbstractDatabaseTestCase
{
    protected static DbPlatform $platform = DbPlatform::SQLServer;

    protected static string $driver = 'pdo_sqlsrv';

    protected static function setupDatabase(): void {}

    #[Test]
    public function createAndDropSchema(): void
    {
        $schema = self::$db->getSchemaManager('ww_tmp_schema', true);

        $drop = "IF EXISTS (SELECT 1 FROM sys.schemas WHERE name = 'ww_tmp_schema') DROP SCHEMA ww_tmp_schema";

        try {
            self::$baseConn->exec($drop);

            self::assertFalse($schema->exists());

            $schema->create();
            $schema->create();

            self::assertTrue($schema->exists());

            $schema->drop();
            $schema->drop();

            self::assertFalse($schema->exists());
        } finally {
            self::$baseConn->exec($drop);
        }
    }

    #[Test]
    public function defaultSchemaExistsAndListsTables(): void
    {
        $schema = self::$db->getSchemaManager('dbo', true);

        self::assertTrue($schema->exists());

        self::$baseConn->exec("IF OBJECT_ID('ww_schema_items', 'U') IS NOT NULL DROP TABLE ww_schema_items");
        self::$baseConn->exec('CREATE TABLE ww_schema_items (id INT)');

        try {
            self::assertTrue($schema->reset()->hasTable('ww_schema_items'));
            self::assertSame('ww_schema_items', $schema->getTable('ww_schema_items')->getName());
        } finally {
            self::$baseConn->exec("IF OBJECT_ID('ww_schema_items', 'U') IS NOT NULL DROP TABLE ww_schema_items");
        }
    }
}
