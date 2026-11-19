<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager;

use PHPUnit\Framework\Attributes\Test;
use Windwalker\Database\Schema\Schema;
use Windwalker\Database\Test\AbstractDatabaseTestCase;

abstract class AbstractTableDiffTestCase extends AbstractDatabaseTestCase
{
    protected static function setupDatabase(): void {}

    protected static bool $virtualGeneratedOnly = false;

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
    public function generatedColumnsAreInSync(): void
    {
        $define = static function (Schema $schema, string $expression = 'price * 2') {
            $schema->primary('id');
            $schema->integer('price');
            $column = $schema->integer('double_price');

            if (static::$virtualGeneratedOnly) {
                $column->virtual($expression);
            } else {
                $column->stored($expression);
            }
        };

        $table = self::$db->getTableManager('diff_generated');
        $table->drop();
        $table->create(static fn(Schema $schema) => $define($schema));

        $same = $table->diff(static fn(Schema $schema) => $define($schema));

        self::assertTrue($same->isEmpty(), json_encode($same->getColumnChanges()));

        $changed = $table->diff(static fn(Schema $schema) => $define($schema, 'price * 3'));

        self::assertSame(['double_price'], array_keys($changed->modifiedColumns));
        self::assertArrayHasKey('generation', $changed->getColumnChanges()['double_price']);

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
        self::assertStringContainsStringIgnoringCase('subtitle', $sql[0]);
        self::assertMatchesRegularExpression('/INDEX/', implode("\n", $sql));
        self::assertFalse($table->hasColumn('subtitle'));
        self::assertFalse($table->hasIndex('idx_diff_subtitle'));

        $table->drop();
    }

    #[Test]
    public function fractionalSecondsColumnsAreInSync(): void
    {
        $define = static function (Schema $schema) {
            $schema->primary('id');
            $schema->datetime('updated')->length(6);
        };

        $table = self::$db->getTableManager('diff_fraction_test');
        $table->drop();
        $table->create($define);

        $diff = $table->diff($define);

        self::assertTrue($diff->isEmpty(), json_encode($diff->getColumnChanges()));

        $table->drop();
    }
}
