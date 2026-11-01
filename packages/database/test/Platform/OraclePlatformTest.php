<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Platform;

use PHPUnit\Framework\Attributes\Test;
use Windwalker\Database\Platform\DbPlatform;

class OraclePlatformTest extends AbstractPlatformTestCase
{
    protected static DbPlatform $platform = DbPlatform::Oracle;

    protected static string $driver = 'pdo_oci';

    #[Test]
    public function getCurrentDatabase(): void
    {
        self::assertEquals('windwalker', $this->instance->getCurrentDatabase());
    }

    #[Test]
    public function createDropDatabase(): void
    {
        self::markTestSkipped('Creating an Oracle user requires DBA privileges.');
    }

    #[Test]
    public function createDropSchema(): void
    {
        self::markTestSkipped('Creating an Oracle user requires DBA privileges.');
    }

    #[Test]
    public function getTables(): void
    {
        self::assertSame(['ww_articles', 'ww_categories'], array_keys($this->instance->listTables()));
    }

    #[Test]
    public function getViews(): void
    {
        self::assertSame(['ww_articles_view'], array_keys($this->instance->listViews()));
    }

    #[Test]
    public function getColumns(): void
    {
        $columns = $this->instance->listColumns('ww_articles');

        self::assertCount(14, $columns);
        self::assertSame('int', $columns['id']['data_type']);
        self::assertTrue($columns['id']['auto_increment']);
        self::assertFalse($columns['id']['is_nullable']);
        self::assertSame('varchar', $columns['title']['data_type']);
        self::assertSame(255, $columns['title']['character_maximum_length']);
        self::assertSame('char', $columns['type']['data_type']);
        self::assertSame('bar', $columns['type']['column_default']);
        self::assertSame('decimal', $columns['price']['data_type']);
        self::assertSame(20, $columns['price']['numeric_precision']);
        self::assertSame(6, $columns['price']['numeric_scale']);
        self::assertSame('text', $columns['introtext']['data_type']);
        self::assertSame('tinyint', $columns['state']['data_type']);
        self::assertSame('timestamp', $columns['created']['data_type']);
    }

    #[Test]
    public function getConstraints(): void
    {
        $constraints = $this->instance->listConstraints('ww_articles');

        self::assertSame('PRIMARY KEY', $constraints['pk_ww_articles']['constraint_type']);
        self::assertSame(['id'], $constraints['pk_ww_articles']['columns']);

        $fk = $constraints['fk_articles_category_id'];

        self::assertSame('FOREIGN KEY', $fk['constraint_type']);
        self::assertSame('ww_categories', $fk['referenced_table_name']);
        self::assertSame(['id'], $fk['referenced_columns']);
        self::assertSame('SET NULL', $fk['delete_rule']);

        self::assertSame(
            ['page_id', 'created_by'],
            $constraints['fk_articles_category_more']['columns']
        );
    }

    #[Test]
    public function getIndexes(): void
    {
        $indexes = $this->instance->listIndexes('ww_articles');

        self::assertTrue($indexes['idx_articles_alias']['is_unique']);
        self::assertSame(['type', 'alias'], array_keys($indexes['idx_articles_alias']['columns']));
        self::assertFalse($indexes['idx_articles_category_id']['is_unique']);
        self::assertTrue($indexes['pk_ww_articles']['is_primary']);
    }
}
