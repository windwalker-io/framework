<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use Windwalker\Database\Manager\TableManager;
use Windwalker\Database\Schema\Ddl\Column;
use Windwalker\Database\Schema\Ddl\Constraint;
use Windwalker\Database\Schema\Ddl\Index;
use Windwalker\Database\Schema\Schema;
use Windwalker\Database\Test\AbstractDatabaseTestCase;

/**
 * Platform-agnostic TableManager behaviors. Subclasses choose the platform/driver and how to drop a table.
 */
abstract class AbstractTableManagerExtraTestCase extends AbstractDatabaseTestCase
{
    protected const string TABLE = 'ww_tm_extra';

    /**
     * SQLite cannot alter columns in place.
     */
    protected static bool $supportsColumnAlter = true;

    /**
     * SQLite cannot add constraints to an existing table.
     */
    protected static bool $supportsAddConstraint = true;

    /**
     * A valid collation name of the platform, used to test column level collation.
     */
    protected static string $collation = 'utf8mb4_bin';

    /**
     * SQL Server has no functional index.
     */
    protected static bool $supportsFunctionalIndex = true;

    protected static string $storedKeyword = 'STORED';

    protected TableManager $table;

    abstract protected static function dropTable(string $table): void;

    protected static function setupDatabase(): void {}

    #[Before]
    protected function createTable(): void
    {
        $this->dropTables();

        $this->table = self::$db->getTableManager(self::TABLE, true);
        $this->table->create(
            static function (Schema $schema) {
                $schema->primary('id');
                $schema->varchar('title');
                $schema->varchar('alias');
                $schema->addIndex('title');
                $schema->addUniqueKey('alias');
            }
        );
    }

    #[After]
    protected function dropTables(): void
    {
        static::dropTable(self::TABLE);
        static::dropTable(self::TABLE . '_renamed');
    }

    #[Test]
    public function createSkipsExistingTable(): void
    {
        $logs = $this->logQueries(
            fn() => $this->table->create(static fn(Schema $schema) => $schema->primary('id'))
        );

        foreach ($logs as $log) {
            self::assertStringNotContainsStringIgnoringCase('CREATE TABLE', $log);
        }
    }

    #[Test]
    public function saveSyncsExistingTable(): void
    {
        $callback = static function (Schema $schema) {
            $schema->primary('id');
            $schema->varchar('title');
            $schema->varchar('alias');
            $schema->varchar('extra')->nullable(true);
            $schema->addIndex('title');
            $schema->addUniqueKey('alias');
        };

        if (!static::$supportsColumnAlter) {
            $this->expectException(\LogicException::class);
        }

        $this->table->save($callback);
        $this->table->reset();

        self::assertTrue($this->table->hasColumn('extra'));
        self::assertTrue($this->table->hasIndex('idx_ww_tm_extra_title'));
    }

    #[Test]
    public function columnsAndKeysCanBeSyncedWithRealColumns(): void
    {
        $columns = $this->table->getColumns(true, true);

        self::assertArrayHasKey('id', $columns);

        $indexes = $this->table->getIndexes(true, true);
        $constraints = $this->table->getConstraints(true, true);

        self::assertNotEmpty($indexes);
        self::assertNotEmpty($constraints);
        self::assertContainsOnlyInstancesOf(
            Column::class,
            array_values(reset($constraints)->columns)
        );
    }

    #[Test]
    public function dropColumnIgnoresMissingColumns(): void
    {
        if (!static::$supportsColumnAlter) {
            $this->expectException(\LogicException::class);
        }

        $this->table->dropColumn(['title', 'not_exists_column']);

        self::assertFalse($this->table->hasColumn('title'));
    }

    #[Test]
    public function dropColumnWithIndexAndConstraint(): void
    {
        if (!static::$supportsColumnAlter) {
            $this->expectException(\Throwable::class);
        }

        $this->table->dropColumn('alias');

        self::assertFalse($this->table->hasColumn('alias'));
        self::assertTrue($this->table->hasColumn('title'));
    }

    #[Test]
    public function modifyColumnCallbackAppliesChanges(): void
    {
        if (!static::$supportsColumnAlter) {
            $this->expectException(\LogicException::class);
        }

        $this->table->modifyColumnCallback(
            'title',
            static fn($column) => $column->length(100)
        );

        $this->table->reset();

        self::assertSame(100, (int) $this->table->getColumn('title')->getCharacterMaximumLength());
    }

    #[Test]
    public function columnExpressionContainsCollation(): void
    {
        $platform = self::$db->getPlatform();

        $expr = (string) $platform->getColumnExpression(
            (new Column('name', 'varchar'))->length(100)->collation(static::$collation)
        );

        self::assertStringContainsString('COLLATE', $expr);
        self::assertStringContainsString(static::$collation, $expr);

        $expr = (string) $platform->getColumnExpression((new Column('name', 'varchar'))->length(100));

        self::assertStringNotContainsString('COLLATE', $expr);
    }

    #[Test]
    public function createAndModifyColumnWithCollation(): void
    {
        $this->table->addColumn(
            (new Column('name', 'varchar'))->length(100)->collation(static::$collation)
        );
        $this->table->reset();

        self::assertTrue($this->table->hasColumn('name'));

        if (!static::$supportsColumnAlter) {
            return;
        }

        $this->table->modifyColumn(
            (new Column('title', 'varchar'))->length(100)->collation(static::$collation)
        );
        $this->table->reset();

        self::assertSame(100, (int) $this->table->getColumn('title')->getCharacterMaximumLength());
    }

    #[Test]
    public function addIndexAndConstraintAreIdempotent(): void
    {
        $this->table->addIndex('title');
        $this->table->reset();
        $this->table->addIndex('title');

        if (static::$supportsAddConstraint) {
            $this->table->addConstraint('alias', Constraint::TYPE_UNIQUE);
            $this->table->reset();
            $this->table->addConstraint('alias', Constraint::TYPE_UNIQUE);
        }

        self::assertTrue($this->table->hasIndex('idx_ww_tm_extra_title'));
    }

    #[Test]
    public function renameTableWithoutReturningNewInstance(): void
    {
        $result = $this->table->rename(self::TABLE . '_renamed', false);

        self::assertSame($this->table, $result);
        self::assertSame(self::TABLE . '_renamed', $result->getName());
        self::assertTrue($result->exists());
    }

    #[Test]
    public function erratasCanBeSet(): void
    {
        $this->table->setErratas(['foo' => 'bar']);

        self::assertSame(['foo' => 'bar'], $this->table->getErratas());
    }

    #[Test]
    public function getDatabaseReturnsManager(): void
    {
        $database = $this->table->getDatabase();

        self::assertNotSame('', $database->getName());
    }

    #[Test]
    public function truncateRemovesRows(): void
    {
        self::$baseConn->exec("INSERT INTO ww_tm_extra (title, alias) VALUES ('a', 'a')");

        $this->table->truncate();

        self::assertSame(0, (int) self::$baseConn->query('SELECT COUNT(*) FROM ww_tm_extra')->fetchColumn());
    }

    #[Test]
    public function generatedColumnExpression(): void
    {
        $platform = self::$db->getPlatform();

        $expr = (string) $platform->getColumnExpression(
            (new Column('double_price', 'integer'))->stored('price * 2')
        );

        self::assertStringContainsString('AS (price * 2)', $expr);
        self::assertStringContainsString(static::$storedKeyword, $expr);
        self::assertStringNotContainsString('DEFAULT', $expr);
    }

    #[Test]
    public function createTableWithStoredGeneratedColumn(): void
    {
        $name = 'ww_tm_generated';

        static::dropTable($name);

        try {
            self::$db->getTableManager($name, true)->create(
                static function (Schema $schema) {
                    $schema->integer('price');
                    $schema->integer('double_price')->stored('price * 2');
                }
            );

            self::$baseConn->exec("INSERT INTO $name (price) VALUES (5)");

            $value = self::$baseConn->query("SELECT double_price FROM $name")->fetchColumn();

            self::assertSame(10, (int) $value);
        } finally {
            static::dropTable($name);
        }
    }

    #[Test]
    public function functionalIndex(): void
    {
        $index = (new Index('idx_ww_lower_title'))->expression('LOWER(title)');

        if (!static::$supportsFunctionalIndex) {
            $this->expectException(LogicException::class);

            $this->table->addIndex($index);

            return;
        }

        $this->table->addIndex($index);
        $this->table->reset();

        self::assertTrue($this->table->hasIndex('idx_ww_lower_title'));
    }
}
