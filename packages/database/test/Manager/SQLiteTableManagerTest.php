<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use Windwalker\Database\Manager\TableManager;
use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Schema\Ddl\Constraint;
use Windwalker\Database\Schema\Schema;
use Windwalker\Database\Test\AbstractDatabaseTestCase;
use Windwalker\Utilities\Str;

class SQLiteTableManagerTest extends AbstractDatabaseTestCase
{
    protected static DbPlatform $platform = DbPlatform::SQLite;

    protected static string $driver = 'pdo_sqlite';

    protected ?TableManager $instance;

    /**
     * @see  TableManager::create
     */
    #[Test]
    public function create(): void
    {
        $table = self::$db->getTableManager('enterprise');

        $logs = $this->logQueries(
            fn() => $table->create(
                static function (Schema $schema) {
                    $schema->primary('id');
                    $schema->char('type')->length(25);
                    $schema->integer('catid')->nullable(true);
                    $schema->varchar('alias');
                    $schema->varchar('title')->defaultValue('H');
                    $schema->decimal('price')->length('20,6');
                    $schema->text('intro');
                    $schema->text('fulltext');
                    $schema->datetime('start_date');
                    $schema->datetime('created');
                    $schema->timestamp('updated')
                        ->onUpdateCurrent()
                        ->defaultCurrent();
                    $schema->timestamp('deleted');
                    $schema->json('params');

                    $schema->addIndex(['catid', 'type']);
                    $schema->addIndex('title(150)');
                    $schema->addUniqueKey('alias');
                }
            )
        );

        self::assertSqlFormatEquals(
            <<<SQL
            SELECT `name`       AS `TABLE_NAME`,
                   'BASE TABLE' AS TABLE_TYPE,
                   'main'       AS TABLE_SCHEMA,
                   NULL         AS VIEW_DEFINITION,
                   NULL         AS CHECK_OPTION,
                   NULL         AS IS_UPDATABLE,
                   `sql`
            FROM `main`.`sqlite_master`
            WHERE `type` = 'table'
              AND `name` NOT LIKE 'sqlite_%'
            ORDER BY `name`;
            SELECT `name` AS `TABLE_NAME`,
                   'VIEW' AS TABLE_TYPE,
                   ''     AS TABLE_SCHEMA,
                   NULL   AS VIEW_DEFINITION,
                   'NONE' AS CHECK_OPTION,
                   NULL   AS IS_UPDATABLE,
                   `sql`
            FROM `sqlite_master`
            WHERE `type` = 'view'
              AND `name` NOT LIKE 'sqlite_%'
            ORDER BY `name`;
            CREATE TABLE IF NOT EXISTS `enterprise` (
             `id` integer NOT NULL,
            `type` char(25) NOT NULL DEFAULT '',
            `catid` integer DEFAULT NULL,
            `alias` varchar(255) NOT NULL DEFAULT '',
            `title` varchar(255) NOT NULL DEFAULT 'H',
            `price` decimal(20,6) NOT NULL DEFAULT '0',
            `intro` text NOT NULL DEFAULT '',
            `fulltext` text NOT NULL DEFAULT '',
            `start_date` datetime DEFAULT NULL,
            `created` datetime DEFAULT NULL,
            `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `deleted` timestamp NOT NULL DEFAULT 1,
            `params` json DEFAULT NULL,
            CONSTRAINT `idx_enterprise_alias` UNIQUE (`alias`),
            CONSTRAINT `pk_enterprise` PRIMARY KEY (`id` AUTOINCREMENT)
            );
            CREATE INDEX `idx_enterprise_catid_type` ON `enterprise` (`catid`,`type`);
            CREATE INDEX `idx_enterprise_title` ON `enterprise` (`title`)
            SQL,
            implode(";\n", $logs)
        );

        self::assertArrayHasKey('enterprise', $table->getPlatform()->listTables());
    }

    /**
     * @see  TableManager::getConstraints
     */
    #[Test]
    public function getConstraints(): void
    {
        $constraints = $this->instance->getConstraints();
        $constraints = array_filter($constraints, fn(Constraint $item) => $item->constraintType !== 'CHECK');

        self::assertEquals(
            ['sqlite_autoindex_enterprise_1'],
            array_keys($constraints)
        );
    }

    /**
     * @see  TableManager::getDetail
     */
    #[Test]
    public function getDetail(): void
    {
        $detail = $this->instance->getDetail();

        self::assertEquals(
            [
                'TABLE_NAME' => 'enterprise',
                'TABLE_SCHEMA' => 'main',
                'TABLE_TYPE' => 'BASE TABLE',
                'VIEW_DEFINITION' => null,
                'CHECK_OPTION' => null,
                'IS_UPDATABLE' => null,
                'sql' => Str::replaceCRLF(
                    <<<SQL
                    CREATE TABLE `enterprise` (
                    `id` integer NOT NULL,
                    `type` char(25) NOT NULL DEFAULT '',
                    `catid` integer DEFAULT NULL,
                    `alias` varchar(255) NOT NULL DEFAULT '',
                    `title` varchar(255) NOT NULL DEFAULT 'H',
                    `price` decimal(20,6) NOT NULL DEFAULT '0',
                    `intro` text NOT NULL DEFAULT '',
                    `fulltext` text NOT NULL DEFAULT '',
                    `start_date` datetime DEFAULT NULL,
                    `created` datetime DEFAULT NULL,
                    `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `deleted` timestamp NOT NULL DEFAULT 1,
                    `params` json DEFAULT NULL,
                    CONSTRAINT `idx_enterprise_alias` UNIQUE (`alias`),
                    CONSTRAINT `pk_enterprise` PRIMARY KEY (`id` AUTOINCREMENT)
                    )
                    SQL
                ),
            ],
            $detail
        );
    }

    /**
     * @see  TableManager::update
     */
    #[Test]
    public function update(): void
    {
        self::markTestIncomplete('Current SQLitePlatform not support schema change');
    }

    /**
     * @see  TableManager::addIndex
     */
    #[Test]
    public function addIndex(): void
    {
        $logs = $this->logQueries(
            function () {
                $this->instance->addIndex('created');
                $this->instance->addIndex(['start_date', 'price']);
            }
        );

        self::assertSqlFormatEquals(
            <<<SQL
            PRAGMA table_info('enterprise');
            PRAGMA table_info('enterprise');
            PRAGMA index_list('enterprise');
            PRAGMA index_info('idx_enterprise_title');
            PRAGMA index_info('idx_enterprise_catid_type');
            PRAGMA index_info('sqlite_autoindex_enterprise_1');
            CREATE INDEX `idx_enterprise_created` ON `enterprise` (`created`);
            CREATE INDEX `idx_enterprise_start_date_price` ON `enterprise` (`start_date`,`price`)
            SQL,
            implode(";\n", $logs)
        );

        $this->instance->reset();

        self::assertEquals(
            [
                'idx_enterprise_start_date_price',
                'idx_enterprise_created',
                'idx_enterprise_title',
                'idx_enterprise_catid_type',
                'sqlite_autoindex_enterprise_1',
            ],
            array_keys($this->instance->getIndexes())
        );
    }

    /**
     * @see  TableManager::hasConstraint
     */
    #[Test]
    public function hasConstraint(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  TableManager::createSchemaObject
     */
    #[Test]
    public function getSchemaObject(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  TableManager::hasIndex
     */
    #[Test]
    public function hasIndex(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  TableManager::dropIndex
     */
    #[Test]
    public function dropIndex(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  TableManager::addConstraint
     */
    #[Test]
    public function addConstraint(): void
    {
        self::markTestIncomplete('Current SQLitePlatform not support schema change');
    }

    /**
     * @see  TableManager::dropColumn
     */
    #[Test]
    public function dropColumn(): void
    {
        self::markTestIncomplete('Current SQLitePlatform not support schema change');
    }

    /**
     * @see  TableManager::modifyColumn
     */
    #[Test]
    public function modifyColumn(): void
    {
        self::markTestIncomplete('Current SQLitePlatform not support schema change');
    }

    /**
     * @see  TableManager::save
     */
    #[Test]
    public function save(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  TableManager::addColumn
     */
    #[Test]
    public function addColumn(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  TableManager::truncate
     */
    #[Test]
    public function truncate(): void
    {
        $logs = $this->logQueries(fn() => $this->instance->truncate());

        self::assertEquals('DELETE FROM `enterprise`', $logs[0]);
    }

    /**
     * @see  TableManager::getColumns
     */
    #[Test]
    public function getColumns(): void
    {
        $cols = array_keys($this->instance->reset()->getColumns());

        self::assertEquals(
            [
                'id',
                'type',
                'catid',
                'alias',
                'title',
                'price',
                'intro',
                'fulltext',
                'start_date',
                'created',
                'updated',
                'deleted',
                'params',
            ],
            $cols
        );
    }

    /**
     * @see  TableManager::setName
     */
    #[Test]
    public function setName(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  TableManager::hasColumn
     */
    #[Test]
    public function hasColumn(): void
    {
        self::assertTrue($this->instance->hasColumn('alias'));
        self::assertFalse($this->instance->hasColumn('enemy'));
    }

    /**
     * @see  TableManager::getColumnNames
     */
    #[Test]
    public function getColumnNames(): void
    {
        self::assertEquals(
            [
                'id',
                'type',
                'catid',
                'alias',
                'title',
                'price',
                'intro',
                'fulltext',
                'start_date',
                'created',
                'updated',
                'deleted',
                'params',
            ],
            $this->instance->getColumnNames()
        );
    }

    /**
     * @see  TableManager::getIndex
     */
    #[Test]
    public function getIndex(): void
    {
        $index = $this->instance->getIndex('idx_enterprise_catid_type');

        self::assertEquals(
            'idx_enterprise_catid_type',
            $index->indexName,
        );

        self::assertEquals(
            ['catid', 'type'],
            array_keys($index->getColumns())
        );
    }

    /**
     * @see  TableManager::reset
     */
    #[Test]
    public function reset(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  TableManager::exists
     */
    #[Test]
    public function exists(): void
    {
        self::assertTrue($this->instance->exists());
        self::assertFalse(self::$db->getTableManager('enterprise_j')->exists());
    }

    /**
     * @see  TableManager::getColumn
     */
    #[Test]
    public function getColumn(): void
    {
        $column = $this->instance->reset()->getColumn('updated');

        self::assertEquals('updated', $column->columnName);
        self::assertEquals('timestamp', $column->getDataType());
    }

    /**
     * @see  TableManager::getConstraint
     */
    #[Test]
    public function getConstraint(): void
    {
        self::markTestIncomplete('Currently SQLitePlatform cannot get constraints'); // TODO: Complete this test
    }

    /**
     * @see  TableManager::getIndexes
     */
    #[Test]
    public function getIndexes(): void
    {
        $indexes = array_keys($this->instance->getIndexes());

        self::assertEquals(
            [
                'idx_enterprise_start_date_price',
                'idx_enterprise_created',
                'idx_enterprise_title',
                'idx_enterprise_catid_type',
                'sqlite_autoindex_enterprise_1',
            ],
            $indexes
        );
    }

    #[Test]
    public function getDatabase(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    #[Test]
    public function getSchema(): void
    {
        $this->instance->schemaName = $this->instance->getPlatform()::getDefaultSchema();

        $logs = $this->logQueries(fn() => $this->instance->reset()->getColumns());

        self::assertSqlFormatEquals(
            <<<SQL
            PRAGMA `main`.table_info('enterprise')
            SQL,
            $logs[0]
        );
    }

    /**
     * @see  TableManager::dropConstraint
     */
    #[Test]
    public function dropConstraint(): void
    {
        self::markTestIncomplete('Currently SQLitePlatform not support schema change');
    }

    #[Test]
    public function renameColumn(): void
    {
        self::markTestIncomplete('Currently SQLitePlatform not support schema change');
    }

    /**
     * @see  TableManager::rename
     */
    #[Test]
    public function rename(): void
    {
        $newTable = $this->instance->rename('enterprise_d');

        self::assertNotSame($newTable, $this->instance);

        self::assertEquals('enterprise_d', $newTable->getName());

        self::assertTrue($newTable->exists());
        self::assertFalse($this->instance->exists());

        $this->instance = $newTable;
    }

    /**
     * @see  TableManager::drop
     */
    #[Test]
    public function drop(): void
    {
        $this->instance->setName('enterprise_d')->reset();
        $this->instance->drop();

        self::assertNotContains(
            'enterprise_d',
            $this->instance->getPlatform()->listTables()
        );
    }

    /**
     * @inheritDoc
     */
    protected static function setupDatabase(): void {}

    #[Before]
    protected function initializeTableManager(): void
    {
        $this->instance = self::$db->getTableManager('enterprise');
    }
}
