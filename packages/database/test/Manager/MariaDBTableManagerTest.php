<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use Windwalker\Database\Manager\TableManager;
use Windwalker\Database\Schema\Ddl\Constraint;
use Windwalker\Database\Schema\Schema;
use Windwalker\Database\Test\AbstractDatabaseTestCase;

class MariaDBTableManagerTest extends AbstractDatabaseTestCase
{
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
            SELECT `TABLE_NAME`,
                   `TABLE_SCHEMA`,
                   `TABLE_TYPE`,
                   NULL AS VIEW_DEFINITION,
                   NULL AS CHECK_OPTION,
                   NULL AS IS_UPDATABLE
            FROM `INFORMATION_SCHEMA`.`TABLES`
            WHERE `TABLE_TYPE` = 'BASE TABLE'
              AND `TABLE_SCHEMA` = (
                SELECT DATABASE()
            );
            SELECT `TABLE_NAME`,
                   `TABLE_SCHEMA`,
                   'VIEW' AS TABLE_TYPE,
                   `VIEW_DEFINITION`,
                   `CHECK_OPTION`,
                   `IS_UPDATABLE`
            FROM `INFORMATION_SCHEMA`.`VIEWS`
            WHERE `TABLE_SCHEMA` = (
                SELECT DATABASE()
            );
            CREATE TABLE IF NOT EXISTS `enterprise` (
            `id` INT(11) NOT NULL,
            `type` CHAR(25) NOT NULL DEFAULT '',
            `catid` INT(11) DEFAULT NULL,
            `alias` VARCHAR(255) NOT NULL DEFAULT '',
            `title` VARCHAR(255) NOT NULL DEFAULT 'H',
            `price` DECIMAL(20,6) NOT NULL DEFAULT 0,
            `intro` text NOT NULL,
            `fulltext` text NOT NULL,
            `start_date` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
            `created` datetime NOT NULL DEFAULT '1000-01-01 00:00:00',
            `updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `deleted` TIMESTAMP NOT NULL DEFAULT '1970-01-01 12:00:01',
            `params` json NOT NULL
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ALTER TABLE `enterprise` ADD CONSTRAINT `pk_enterprise` PRIMARY KEY (`id`);
            ALTER TABLE `enterprise` MODIFY COLUMN `id` INT(11) NOT NULL AUTO_INCREMENT;
            ALTER TABLE `enterprise` ADD INDEX `idx_enterprise_catid_type` (`catid`,`type`);
            ALTER TABLE `enterprise` ADD INDEX `idx_enterprise_title` (`title`(150));
            ALTER TABLE `enterprise` ADD CONSTRAINT `idx_enterprise_alias` UNIQUE (`alias`)
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
            ['PRIMARY', 'idx_enterprise_alias'],
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
                'TABLE_SCHEMA' => 'windwalker_test',
                'TABLE_TYPE' => 'BASE TABLE',
                'VIEW_DEFINITION' => null,
                'CHECK_OPTION' => null,
                'IS_UPDATABLE' => null,
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
        $logs = $this->logQueries(
            fn() => $this->instance->update(
                function (Schema $schema) {
                    // New column
                    $schema->varchar('captain')->length(512)->after('catid');
                    $schema->varchar('first_officer')->length(512)->after('captain');

                    // Update column
                    $schema->char('alias')->length(25)
                        ->nullable(true)
                        ->defaultValue('');

                    // New index
                    $schema->addIndex('captain');
                }
            )
        );

        self::assertSqlFormatEquals(
            <<<SQL
            SELECT `ORDINAL_POSITION`,
                   `COLUMN_DEFAULT`,
                   `IS_NULLABLE`,
                   `DATA_TYPE`,
                   `CHARACTER_MAXIMUM_LENGTH`,
                   `CHARACTER_OCTET_LENGTH`,
                   `NUMERIC_PRECISION`,
                   `NUMERIC_SCALE`,
                   `COLUMN_NAME`,
                   `COLUMN_TYPE`,
                   `COLUMN_COMMENT`,
                   `EXTRA`
            FROM `INFORMATION_SCHEMA`.`COLUMNS`
            WHERE `TABLE_NAME` = 'enterprise'
              AND `TABLE_SCHEMA` = (SELECT DATABASE());
            ALTER TABLE `enterprise`
                ADD COLUMN `captain` varchar(512) NOT NULL DEFAULT '';
            ALTER TABLE `enterprise`
                ADD COLUMN `first_officer` varchar(512) NOT NULL DEFAULT '';
            ALTER TABLE `enterprise`
                MODIFY COLUMN `alias` CHAR(25) DEFAULT '';
            SELECT `TABLE_SCHEMA`,
                   `TABLE_NAME`,
                   `NON_UNIQUE`,
                   `INDEX_NAME`,
                   `COLUMN_NAME`,
                   `COLLATION`,
                   `CARDINALITY`,
                   `SUB_PART`,
                   `INDEX_COMMENT`
            FROM `INFORMATION_SCHEMA`.`STATISTICS`
            WHERE `TABLE_NAME` = 'enterprise'
              AND `TABLE_SCHEMA` = (SELECT DATABASE());
            ALTER TABLE `enterprise`
                ADD INDEX `idx_enterprise_captain` (`captain`)
            SQL,
            implode("\n;", $logs)
        );
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
                $this->instance->addIndex(['start_date', 'params']);
            }
        );

        self::assertSqlFormatEquals(
            <<<SQL
            SELECT `ORDINAL_POSITION`,
                   `COLUMN_DEFAULT`,
                   `IS_NULLABLE`,
                   `DATA_TYPE`,
                   `CHARACTER_MAXIMUM_LENGTH`,
                   `CHARACTER_OCTET_LENGTH`,
                   `NUMERIC_PRECISION`,
                   `NUMERIC_SCALE`,
                   `COLUMN_NAME`,
                   `COLUMN_TYPE`,
                   `COLUMN_COMMENT`,
                   `EXTRA`
            FROM `INFORMATION_SCHEMA`.`COLUMNS`
            WHERE `TABLE_NAME` = 'enterprise'
              AND `TABLE_SCHEMA` = (SELECT DATABASE());
            SELECT `TABLE_SCHEMA`,
                   `TABLE_NAME`,
                   `NON_UNIQUE`,
                   `INDEX_NAME`,
                   `COLUMN_NAME`,
                   `COLLATION`,
                   `CARDINALITY`,
                   `SUB_PART`,
                   `INDEX_COMMENT`
            FROM `INFORMATION_SCHEMA`.`STATISTICS`
            WHERE `TABLE_NAME` = 'enterprise'
              AND `TABLE_SCHEMA` = (SELECT DATABASE());
            ALTER TABLE `enterprise`
                ADD INDEX `idx_enterprise_created` (`created`);
            ALTER TABLE `enterprise`
                ADD INDEX `idx_enterprise_start_date_params` (`start_date`, `params`(150))
            SQL,
            implode(";\n", $logs)
        );

        $this->instance->reset();

        self::assertEquals(
            [
                'PRIMARY',
                'idx_enterprise_alias',
                'idx_enterprise_catid_type',
                'idx_enterprise_title',
                'idx_enterprise_captain',
                'idx_enterprise_created',
                'idx_enterprise_start_date_params',
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
        $logs = $this->logQueries(
            fn() => $this->instance->addConstraint(
                ['captain', 'first_officer'],
                Constraint::TYPE_UNIQUE
            )
        );

        self::assertSqlFormatEquals(
            <<<SQL
SELECT `ORDINAL_POSITION`,
       `COLUMN_DEFAULT`,
       `IS_NULLABLE`,
       `DATA_TYPE`,
       `CHARACTER_MAXIMUM_LENGTH`,
       `CHARACTER_OCTET_LENGTH`,
       `NUMERIC_PRECISION`,
       `NUMERIC_SCALE`,
       `COLUMN_NAME`,
       `COLUMN_TYPE`,
       `COLUMN_COMMENT`,
       `EXTRA`
FROM `INFORMATION_SCHEMA`.`COLUMNS`
WHERE `TABLE_NAME` = 'enterprise'
  AND `TABLE_SCHEMA` = (SELECT DATABASE());
SELECT `TABLE_NAME`, `CONSTRAINT_NAME`, `CONSTRAINT_TYPE`
FROM `INFORMATION_SCHEMA`.`TABLE_CONSTRAINTS`
WHERE `TABLE_NAME` = 'enterprise'
  AND `TABLE_SCHEMA` = (SELECT DATABASE());
SELECT `CONSTRAINT_NAME`,
       `COLUMN_NAME`,
       `REFERENCED_TABLE_SCHEMA`,
       `REFERENCED_TABLE_NAME`,
       `REFERENCED_COLUMN_NAME`,
       `REFERENCED_COLUMN_NAME`
FROM `INFORMATION_SCHEMA`.`KEY_COLUMN_USAGE`
WHERE `TABLE_NAME` = 'enterprise'
  AND `TABLE_SCHEMA` = (SELECT DATABASE());
SELECT `CONSTRAINT_NAME`, `MATCH_OPTION`, `UPDATE_RULE`, `DELETE_RULE`
FROM `INFORMATION_SCHEMA`.`REFERENTIAL_CONSTRAINTS`
WHERE `TABLE_NAME` = 'enterprise'
  AND `CONSTRAINT_SCHEMA` = (SELECT DATABASE());
ALTER TABLE `enterprise`
    ADD CONSTRAINT `ct_enterprise_captain_first_officer` UNIQUE (`captain`(150),`first_officer`(150))
SQL,
            implode(";\n", $logs)
        );
    }

    /**
     * @see  TableManager::dropColumn
     */
    #[Test]
    public function dropColumn(): void
    {
        $this->instance->reset();
        $this->instance->dropColumn(['captain', 'first_officer']);

        $this->instance->reset();

        self::assertFalse($this->instance->hasColumn('captain'));
        self::assertFalse($this->instance->hasColumn('first_officer'));
    }

    /**
     * @see  TableManager::modifyColumn
     */
    #[Test]
    public function modifyColumn(): void
    {
        $this->instance->modifyColumn(
            'price',
            'decimal(15,4)',
            true,
            100.5
        );

        $this->instance->reset();
        $column = $this->instance->getColumn('price');

        self::assertEquals('15,4', $column->getLengthExpression());
        self::assertEquals('decimal', $column->getDataType());
        self::assertEquals(100.5, $column->getColumnDefault());
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

        self::assertEquals('TRUNCATE TABLE `enterprise`', $logs[0]);
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
        $index = $this->instance->getIndex('idx_enterprise_start_date_params');

        self::assertEquals(
            'idx_enterprise_start_date_params',
            $index->indexName,
        );

        self::assertEquals(
            ['start_date', 'params'],
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
        self::assertEquals('current_timestamp()', $column->getErratas()['on_update']);
    }

    /**
     * @see  TableManager::getConstraint
     */
    #[Test]
    public function getConstraint(): void
    {
        $constraint = $this->instance->reset()->getConstraint('idx_enterprise_alias');

        self::assertEquals(
            'idx_enterprise_alias',
            $constraint->constraintName
        );

        self::assertEquals(['alias'], array_keys($constraint->getColumns()));
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
                'PRIMARY',
                'idx_enterprise_alias',
                'idx_enterprise_catid_type',
                'idx_enterprise_title',
                'idx_enterprise_created',
                'idx_enterprise_start_date_params',
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
        $this->instance->schemaName = self::$db->getOption('database');

        $logs = $this->logQueries(fn() => $this->instance->getColumns());

        self::assertSqlFormatEquals(
            <<<SQL
            SELECT `ORDINAL_POSITION`,
                   `COLUMN_DEFAULT`,
                   `IS_NULLABLE`,
                   `DATA_TYPE`,
                   `CHARACTER_MAXIMUM_LENGTH`,
                   `CHARACTER_OCTET_LENGTH`,
                   `NUMERIC_PRECISION`,
                   `NUMERIC_SCALE`,
                   `COLUMN_NAME`,
                   `COLUMN_TYPE`,
                   `COLUMN_COMMENT`,
                   `EXTRA`
            FROM `INFORMATION_SCHEMA`.`COLUMNS`
            WHERE `TABLE_NAME` = 'enterprise'
              AND `TABLE_SCHEMA` = 'windwalker_test'
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
        $this->instance->dropConstraint('idx_enterprise_alias');

        self::assertEquals(
            ['PRIMARY', 'params'],
            array_keys($this->instance->reset()->getConstraints())
        );
    }

    #[Test]
    public function renameColumn(): void
    {
        $this->instance->renameColumn('type', 'kind');

        self::assertEquals(
            'kind',
            $this->instance->reset()->getColumn('kind')->getColumnName()
        );
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

        self::markTestSkipped();
    }
}
