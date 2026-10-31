<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use Windwalker\Database\Manager\TableManager;
use Windwalker\Database\Schema\Schema;
use Windwalker\Database\Test\AbstractDatabaseTestCase;

class MySQLTableManagerExtraTest extends AbstractDatabaseTestCase
{
    private const string TABLE = 'ww_tm_extra';

    private TableManager $table;

    protected static function setupDatabase(): void {}

    #[Before]
    protected function createTable(): void
    {
        self::$baseConn->exec('DROP TABLE IF EXISTS ' . self::TABLE);
        self::$baseConn->exec('DROP TABLE IF EXISTS ' . self::TABLE . '_renamed');

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
        self::$baseConn->exec('DROP TABLE IF EXISTS ' . self::TABLE);
        self::$baseConn->exec('DROP TABLE IF EXISTS ' . self::TABLE . '_renamed');
    }

    #[Test]
    public function createSkipsExistingTable(): void
    {
        $logs = $this->logQueries(
            fn() => $this->table->create(static fn(Schema $schema) => $schema->primary('id'))
        );

        self::assertFalse($this->containsCreate($logs));
    }

    #[Test]
    public function saveSyncsIndexesAndConstraintsOfExistingTable(): void
    {
        $this->table->save(
            static function (Schema $schema) {
                $schema->primary('id');
                $schema->varchar('title');
                $schema->varchar('alias');
                $schema->varchar('extra')->nullable(true);
                $schema->addIndex('title');
                $schema->addUniqueKey('alias');
            }
        );

        $this->table->reset();

        self::assertTrue($this->table->hasColumn('extra'));
        self::assertTrue($this->table->hasIndex('idx_ww_tm_extra_title'));
    }

    #[Test]
    public function columnsAndKeysCanBeSyncedWithRealColumns(): void
    {
        $columns = $this->table->getColumns(true, true);

        self::assertTrue($columns['id']->isPrimary());

        $indexes = $this->table->getIndexes(true, true);
        $constraints = $this->table->getConstraints(true, true);

        self::assertNotEmpty($indexes);
        self::assertNotEmpty($constraints);
        self::assertContainsOnlyInstancesOf(
            \Windwalker\Database\Schema\Ddl\Column::class,
            array_values(reset($constraints)->columns)
        );
    }

    #[Test]
    public function dropColumnIgnoresMissingColumns(): void
    {
        $this->table->dropColumn(['title', 'not_exists_column']);

        self::assertFalse($this->table->hasColumn('title'));
    }

    #[Test]
    public function modifyColumnCallbackAppliesChanges(): void
    {
        $this->table->modifyColumnCallback(
            'title',
            static fn($column) => $column->length(100)
        );

        $this->table->reset();

        self::assertSame(100, (int) $this->table->getColumn('title')->getCharacterMaximumLength());
    }

    #[Test]
    public function addIndexAndConstraintAreIdempotent(): void
    {
        $this->table->addIndex('title');
        $this->table->addIndex('title');
        $this->table->addConstraint(\Windwalker\Database\Schema\Ddl\Constraint::TYPE_UNIQUE, 'alias');
        $this->table->addConstraint(\Windwalker\Database\Schema\Ddl\Constraint::TYPE_UNIQUE, 'alias');

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
        self::assertSame(
            self::$db->getPlatform()->getCurrentDatabase(),
            $this->table->getDatabase()->getName()
        );
    }

    private function containsCreate(array $logs): bool
    {
        foreach ($logs as $log) {
            if (stripos($log, 'CREATE TABLE') !== false) {
                return true;
            }
        }

        return false;
    }
}
