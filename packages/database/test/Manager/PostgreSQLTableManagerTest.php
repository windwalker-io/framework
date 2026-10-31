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

class PostgreSQLTableManagerTest extends AbstractDatabaseTestCase
{
    protected static DbPlatform $platform = DbPlatform::PostgreSQL;

    protected static string $driver = 'pdo_pgsql';

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
            SELECT "table_name"    AS "TABLE_NAME",
                   "table_catalog" AS "TABLE_CATALOG",
                   "table_schema"  AS "TABLE_SCHEMA",
                   "table_type"    AS "TABLE_TYPE",
                   NULL            AS "VIEW_DEFINITION",
                   NULL            AS "CHECK_OPTION",
                   NULL            AS "IS_UPDATABLE"
            FROM "information_schema"."tables"
            WHERE "table_type" = 'BASE TABLE'
              AND "table_schema" NOT IN ('pg_catalog', 'information_schema')
            ORDER BY "table_name" ASC;
            SELECT "table_name"      AS "TABLE_NAME",
                   "table_catalog"   AS "TABLE_CATALOG",
                   "table_schema"    AS "TABLE_SCHEMA",
                   'VIEW'            AS "TABLE_TYPE",
                   "view_definition" AS "VIEW_DEFINITION",
                   "check_option"    AS "CHECK_OPTION",
                   "is_updatable"    AS "IS_UPDATABLE"
            FROM "information_schema"."views"
            WHERE "table_schema" NOT IN ('pg_catalog', 'information_schema')
            ORDER BY "table_name" ASC;
            CREATE TABLE IF NOT EXISTS "enterprise" (
            "id" serial NOT NULL,
            "type" varchar(25) NOT NULL DEFAULT '',
            "catid" integer DEFAULT NULL,
            "alias" varchar(255) NOT NULL DEFAULT '',
            "title" varchar(255) NOT NULL DEFAULT 'H',
            "price" decimal(20,6) NOT NULL DEFAULT '0',
            "intro" text NOT NULL DEFAULT '',
            "fulltext" text NOT NULL DEFAULT '',
            "start_date" timestamp DEFAULT NULL,
            "created" timestamp DEFAULT NULL,
            "updated" timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            "deleted" timestamp NOT NULL DEFAULT '1970-01-01 00:00:00',
            "params" json DEFAULT NULL
            );
            CREATE INDEX "idx_enterprise_catid_type" ON "enterprise" ("catid","type");
            CREATE INDEX "idx_enterprise_title" ON "enterprise" ("title");
            ALTER TABLE "enterprise"
             ADD CONSTRAINT "pk_enterprise" PRIMARY KEY ("id");
            ALTER TABLE "enterprise"
             ADD CONSTRAINT "idx_enterprise_alias" UNIQUE ("alias")
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
            ['pk_enterprise', 'idx_enterprise_alias'],
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
                'TABLE_SCHEMA' => 'public',
                'TABLE_TYPE' => 'BASE TABLE',
                'TABLE_CATALOG' => 'windwalker_test',
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
        $this->instance->update(
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
        );

        $columns = $this->instance->getColumns(true);

        self::assertEquals(
            'varchar',
            $columns['captain']->getDataType(),
        );

        self::assertEquals(
            512,
            $columns['captain']->getCharacterMaximumLength(),
        );

        self::assertEquals(
            'varchar',
            $columns['first_officer']->getDataType(),
        );

        self::assertEquals(
            'varchar',
            $columns['alias']->getDataType(),
        );

        self::assertEquals(
            25,
            $columns['alias']->getCharacterMaximumLength(),
        );

        self::assertEquals(
            true,
            $columns['alias']->getIsNullable(),
        );

        self::assertEquals(
            '',
            $columns['alias']->getColumnDefault(),
        );
    }

    /**
     * @see  TableManager::addIndex
     */
    #[Test]
    public function addIndex(): void
    {
        $this->instance->addIndex('created');
        $this->instance->addIndex(['start_date', 'first_officer']);

        $this->instance->reset();

        self::assertArraySimilar(
            [
                'pk_enterprise',
                'idx_enterprise_alias',
                'idx_enterprise_catid_type',
                'idx_enterprise_title',
                'idx_enterprise_captain',
                'idx_enterprise_created',
                'idx_enterprise_start_date_first_officer',
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
        $this->instance->addConstraint(
            ['captain', 'first_officer'],
            Constraint::TYPE_UNIQUE
        );

        $this->instance->reset();
        $constraints = array_filter(
            $this->instance->getConstraints(),
            fn(Constraint $item) => $item->constraintType !== 'CHECK'
        );

        self::assertArrayHasKey('ct_enterprise_captain_first_officer', $constraints);
        self::assertEquals(
            Constraint::TYPE_UNIQUE,
            $constraints['ct_enterprise_captain_first_officer']->constraintType
        );
        self::assertEquals(
            ['captain', 'first_officer'],
            array_keys($constraints['ct_enterprise_captain_first_officer']->getColumns())
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
        self::assertEquals('numeric', $column->getDataType());
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

        self::assertEquals('TRUNCATE TABLE "enterprise"', $logs[0]);
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
        self::assertEquals('timestamp without time zone', $column->getDataType());
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
                'pk_enterprise',
                'idx_enterprise_catid_type',
                'idx_enterprise_title',
                'idx_enterprise_alias',
                'idx_enterprise_created',
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

        $logs = $this->logQueries(fn() => $this->instance->getColumns());

        self::assertSqlFormatEquals(
            <<<SQL
            SELECT
              "ordinal_position",
              "column_default",
              "is_nullable",
              "data_type",
              "character_maximum_length",
              "character_octet_length",
              "numeric_precision",
              "numeric_scale",
              "column_name"
            FROM
              "information_schema"."columns"
            WHERE
              "table_name" = 'enterprise'
              AND "table_schema" = 'public'
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
            ['pk_enterprise'],
            array_keys(
                array_filter(
                    $this->instance->reset()->getConstraints(),
                    fn(Constraint $constraint) => $constraint->constraintType !== 'CHECK'
                )
            )
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
    }
}
