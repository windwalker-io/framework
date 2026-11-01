<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager;

use PHPUnit\Framework\Attributes\Test;
use Windwalker\Database\Schema\Schema;
use Windwalker\Database\Test\AbstractDatabaseTestCase;

abstract class AbstractTableDiffTestCase extends AbstractDatabaseTestCase
{
    protected static function setupDatabase(): void {}

    private static function define(Schema $schema, bool $extra = false): void
    {
        $schema->primary('id');
        $schema->varchar('title')->defaultValue('H');
        $schema->decimal('price')->length('20,6');
        $schema->addIndex(['title'], 'idx_diff_title');

        if ($extra) {
            $schema->varchar('subtitle')->defaultValue('');
            $schema->addIndex(['subtitle'], 'idx_diff_subtitle');
        }
    }

    #[Test]
    public function diffAndSync(): void
    {
        $table = self::$db->getTableManager('diff_test');
        $table->drop();
        $table->create(static fn(Schema $schema) => self::define($schema));

        $same = $table->diff(static fn(Schema $schema) => self::define($schema));

        self::assertTrue($same->isEmpty(), json_encode($same->getColumnChanges()));

        $diff = $table->diff(static fn(Schema $schema) => self::define($schema, true));

        self::assertSame(['subtitle'], array_keys($diff->addedColumns));
        self::assertSame(['idx_diff_subtitle'], array_keys($diff->addedIndexes));
        self::assertFalse($table->hasColumn('subtitle'));

        $table->sync(static fn(Schema $schema) => self::define($schema, true));

        self::assertTrue($table->hasColumn('subtitle'));
        self::assertTrue($table->hasIndex('idx_diff_subtitle'));
        self::assertTrue($table->diff(static fn(Schema $schema) => self::define($schema, true))->isEmpty());

        $table->drop();
    }

    #[Test]
    public function syncSqlDoesNotExecute(): void
    {
        $table = self::$db->getTableManager('diff_test');
        $table->drop();
        $table->create(static fn(Schema $schema) => self::define($schema));

        $sql = $table->syncSql(static fn(Schema $schema) => self::define($schema, true));

        self::assertNotEmpty($sql);
        self::assertMatchesRegularExpression('/\\bADD\\b/', $sql[0]);
        self::assertStringContainsString('subtitle', $sql[0]);
        self::assertMatchesRegularExpression('/INDEX/', implode("\n", $sql));
        self::assertFalse($table->hasColumn('subtitle'));
        self::assertFalse($table->hasIndex('idx_diff_subtitle'));

        $table->drop();
    }
}
