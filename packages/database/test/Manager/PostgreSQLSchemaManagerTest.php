<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager;

use PHPUnit\Framework\Attributes\Test;
use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\AbstractDatabaseTestCase;
use Windwalker\ORM\Attributes\Table;

#[Table('ww_schema_attr_items')]
class SchemaAttrEntityFixture {}

class PostgreSQLSchemaManagerTest extends AbstractDatabaseTestCase
{
    protected static DbPlatform $platform = DbPlatform::PostgreSQL;

    protected static string $driver = 'pdo_pgsql';

    protected static string $schema = 'public';

    protected static function setupDatabase(): void {}

    #[Test]
    public function createAndDropSchema(): void
    {
        $schema = self::$db->getSchemaManager('ww_tmp_schema', true);

        try {
            self::$baseConn->exec('DROP SCHEMA IF EXISTS ww_tmp_schema CASCADE');

            self::assertFalse($schema->exists());

            $schema->create();
            $schema->create();

            self::assertTrue($schema->exists());

            $schema->drop();
            $schema->drop();

            self::assertFalse($schema->exists());
        } finally {
            self::$baseConn->exec('DROP SCHEMA IF EXISTS ww_tmp_schema CASCADE');
        }
    }

    #[Test]
    public function getTableResolvesNameFromTableAttribute(): void
    {
        $table = self::$db->getSchemaManager('public', true)->getTable(SchemaAttrEntityFixture::class);

        self::assertSame('ww_schema_attr_items', $table->getName());
    }
}
