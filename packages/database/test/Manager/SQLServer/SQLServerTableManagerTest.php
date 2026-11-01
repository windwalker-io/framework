<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Manager\SQLServer;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use Windwalker\Database\Manager\TableManager;
use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Schema\Ddl\Constraint;
use Windwalker\Database\Schema\Schema;
use Windwalker\Database\Test\AbstractDatabaseTestCase;

class SQLServerTableManagerTest extends AbstractDatabaseTestCase
{
    protected static DbPlatform $platform = DbPlatform::SQLServer;

    protected static string $driver = 'pdo_sqlsrv';

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
            SELECT [TABLE_NAME], [TABLE_SCHEMA], [TABLE_TYPE], NULL AS VIEW_DEFINITION, NULL AS CHECK_OPTION, NULL AS IS_UPDATABLE FROM [INFORMATION_SCHEMA].[TABLES] WHERE [TABLE_TYPE] = 'BASE TABLE' AND [TABLE_SCHEMA] != 'INFORMATION_SCHEMA' ORDER BY [TABLE_NAME];
            SELECT [TABLE_NAME], [TABLE_SCHEMA], 'VIEW' AS TABLE_TYPE, [VIEW_DEFINITION], [CHECK_OPTION], [IS_UPDATABLE] FROM [INFORMATION_SCHEMA].[VIEWS] WHERE [TABLE_SCHEMA] != 'INFORMATION_SCHEMA' ORDER BY [TABLE_NAME];
            CREATE TABLE [enterprise] (
            [id] int NOT NULL IDENTITY,
            [type] nvarchar(25) NOT NULL DEFAULT 0,
            [catid] int DEFAULT NULL,
            [alias] nvarchar(255) NOT NULL DEFAULT 0,
            [title] nvarchar(255) NOT NULL DEFAULT 'H',
            [price] decimal(20,6) NOT NULL DEFAULT '0',
            [intro] nvarchar(max) NOT NULL DEFAULT 0,
            [fulltext] nvarchar(max) NOT NULL DEFAULT 0,
            [start_date] datetime2 DEFAULT NULL,
            [created] datetime2 DEFAULT NULL,
            [updated] datetime2 NOT NULL DEFAULT CURRENT_TIMESTAMP,
            [deleted] datetime2 NOT NULL DEFAULT '1900-01-01 00:00:00',
            [params] nvarchar(max) DEFAULT NULL
            );
            CREATE INDEX [idx_enterprise_catid_type] ON [enterprise] ([catid],[type]);
            CREATE INDEX [idx_enterprise_title] ON [enterprise] ([title]);
            ALTER TABLE [enterprise]
            ADD CONSTRAINT [pk_enterprise] PRIMARY KEY ([id]);
            ALTER TABLE [enterprise]
            ADD CONSTRAINT [idx_enterprise_alias] UNIQUE ([alias])
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
                'TABLE_SCHEMA' => 'dbo',
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

        // phpcs:disable
        self::assertSqlFormatEquals(
            <<<SQL
            SELECT [c].[ORDINAL_POSITION], [c].[COLUMN_DEFAULT], [c].[IS_NULLABLE], [c].[DATA_TYPE], [c].[CHARACTER_MAXIMUM_LENGTH], [c].[DATETIME_PRECISION], [c].[CHARACTER_OCTET_LENGTH], [c].[NUMERIC_PRECISION], [c].[NUMERIC_SCALE], [c].[COLUMN_NAME], [sc].[is_identity] FROM [INFORMATION_SCHEMA].[COLUMNS] AS [c] LEFT JOIN [sys].[columns] AS [sc] ON [sc].[object_id] = object_id(c.TABLE_NAME) AND [sc].[name] = [c].[COLUMN_NAME] WHERE [c].[TABLE_NAME] = 'enterprise' AND [c].[TABLE_SCHEMA] != 'INFORMATION_SCHEMA'
            ;ALTER TABLE [enterprise] ADD [captain] nvarchar(512) NOT NULL DEFAULT 0
            ;ALTER TABLE [enterprise] ADD [first_officer] nvarchar(512) NOT NULL DEFAULT 0
            ;DECLARE @ConstraintName nvarchar(200)

            SELECT @ConstraintName = Name FROM SYS.DEFAULT_CONSTRAINTS
            WHERE PARENT_OBJECT_ID = OBJECT_ID('enterprise')
            AND PARENT_COLUMN_ID = (SELECT column_id FROM sys.columns
                                    WHERE NAME = N'alias'
                                    AND object_id = OBJECT_ID(N'enterprise'))

            IF @ConstraintName IS NOT NULL
            EXEC('ALTER TABLE [enterprise] DROP CONSTRAINT [' + @ConstraintName + ']')
            ;SELECT [T].[TABLE_NAME], [TC].[CONSTRAINT_NAME], [TC].[CONSTRAINT_TYPE], [KCU].[COLUMN_NAME], [CC].[CHECK_CLAUSE], [RC].[MATCH_OPTION], [RC].[UPDATE_RULE], [RC].[DELETE_RULE], [KCU2].[TABLE_SCHEMA] AS [REFERENCED_TABLE_SCHEMA], [KCU2].[TABLE_NAME] AS [REFERENCED_TABLE_NAME], [KCU2].[COLUMN_NAME] AS [REFERENCED_COLUMN_NAME] FROM [INFORMATION_SCHEMA].[TABLES] AS [T] INNER JOIN [INFORMATION_SCHEMA].[TABLE_CONSTRAINTS] AS [TC] ON [T].[TABLE_SCHEMA] = [TC].[TABLE_SCHEMA] AND [T].[TABLE_NAME] = [TC].[TABLE_NAME] LEFT JOIN [INFORMATION_SCHEMA].[KEY_COLUMN_USAGE] AS [KCU] ON [KCU].[TABLE_SCHEMA] = [TC].[TABLE_SCHEMA] AND [KCU].[TABLE_NAME] = [TC].[TABLE_NAME] AND [KCU].[CONSTRAINT_NAME] = [TC].[CONSTRAINT_NAME] LEFT JOIN [INFORMATION_SCHEMA].[CHECK_CONSTRAINTS] AS [CC] ON [CC].[CONSTRAINT_SCHEMA] = [TC].[CONSTRAINT_SCHEMA] AND [CC].[CONSTRAINT_NAME] = [TC].[CONSTRAINT_NAME] LEFT JOIN [INFORMATION_SCHEMA].[REFERENTIAL_CONSTRAINTS] AS [RC] ON [RC].[CONSTRAINT_SCHEMA] = [TC].[CONSTRAINT_SCHEMA] AND [RC].[CONSTRAINT_NAME] = [TC].[CONSTRAINT_NAME] LEFT JOIN [INFORMATION_SCHEMA].[KEY_COLUMN_USAGE] AS [KCU2] ON [RC].[UNIQUE_CONSTRAINT_SCHEMA] = [KCU2].[CONSTRAINT_SCHEMA] AND [RC].[UNIQUE_CONSTRAINT_NAME] = [KCU2].[CONSTRAINT_NAME] AND [KCU].[ORDINAL_POSITION] = [KCU2].[ORDINAL_POSITION] WHERE [T].[TABLE_NAME] = 'enterprise' AND [T].[TABLE_TYPE] IN ('BASE table', 'VIEW') AND [T].[TABLE_SCHEMA] NOT IN ('PG_CATALOG', 'INFORMATION_SCHEMA') ORDER BY CASE [TC].[CONSTRAINT_TYPE] WHEN 'PRIMARY KEY' THEN 1 WHEN 'UNIQUE' THEN 2 WHEN 'FOREIGN KEY' THEN 3 WHEN 'CHECK' THEN 4 ELSE 5 END, [TC].[CONSTRAINT_NAME], [KCU].[ORDINAL_POSITION]
            ;ALTER TABLE [enterprise] DROP CONSTRAINT [idx_enterprise_alias]
            ;SELECT schema_name(tbl.schema_id) AS schema_name, [tbl].[name] AS [table_name], [col].[name] AS [column_name], [idx].[name] AS [index_name], [col].*, [idx].* FROM [sys].[columns] AS [col] LEFT JOIN [sys].[tables] AS [tbl] ON [col].[object_id] = [tbl].[object_id] LEFT JOIN [sys].[index_columns] AS [ic] ON [col].[column_id] = [ic].[column_id] AND [ic].[object_id] = [tbl].[object_id] LEFT JOIN [sys].[indexes] AS [idx] ON [idx].[object_id] = [tbl].[object_id] AND [idx].[index_id] = [ic].[index_id] WHERE [tbl].[name] = 'enterprise' AND ([idx].[name] IS NOT NULL OR [col].[is_identity] = 1 OR [idx].[is_primary_key] = 1)
            ;ALTER TABLE [enterprise] ALTER COLUMN [alias] nvarchar(25) NOT NULL;ALTER TABLE [enterprise] ADD DEFAULT '' FOR [alias]
            ;SELECT [T].[TABLE_NAME], [TC].[CONSTRAINT_NAME], [TC].[CONSTRAINT_TYPE], [KCU].[COLUMN_NAME], [CC].[CHECK_CLAUSE], [RC].[MATCH_OPTION], [RC].[UPDATE_RULE], [RC].[DELETE_RULE], [KCU2].[TABLE_SCHEMA] AS [REFERENCED_TABLE_SCHEMA], [KCU2].[TABLE_NAME] AS [REFERENCED_TABLE_NAME], [KCU2].[COLUMN_NAME] AS [REFERENCED_COLUMN_NAME] FROM [INFORMATION_SCHEMA].[TABLES] AS [T] INNER JOIN [INFORMATION_SCHEMA].[TABLE_CONSTRAINTS] AS [TC] ON [T].[TABLE_SCHEMA] = [TC].[TABLE_SCHEMA] AND [T].[TABLE_NAME] = [TC].[TABLE_NAME] LEFT JOIN [INFORMATION_SCHEMA].[KEY_COLUMN_USAGE] AS [KCU] ON [KCU].[TABLE_SCHEMA] = [TC].[TABLE_SCHEMA] AND [KCU].[TABLE_NAME] = [TC].[TABLE_NAME] AND [KCU].[CONSTRAINT_NAME] = [TC].[CONSTRAINT_NAME] LEFT JOIN [INFORMATION_SCHEMA].[CHECK_CONSTRAINTS] AS [CC] ON [CC].[CONSTRAINT_SCHEMA] = [TC].[CONSTRAINT_SCHEMA] AND [CC].[CONSTRAINT_NAME] = [TC].[CONSTRAINT_NAME] LEFT JOIN [INFORMATION_SCHEMA].[REFERENTIAL_CONSTRAINTS] AS [RC] ON [RC].[CONSTRAINT_SCHEMA] = [TC].[CONSTRAINT_SCHEMA] AND [RC].[CONSTRAINT_NAME] = [TC].[CONSTRAINT_NAME] LEFT JOIN [INFORMATION_SCHEMA].[KEY_COLUMN_USAGE] AS [KCU2] ON [RC].[UNIQUE_CONSTRAINT_SCHEMA] = [KCU2].[CONSTRAINT_SCHEMA] AND [RC].[UNIQUE_CONSTRAINT_NAME] = [KCU2].[CONSTRAINT_NAME] AND [KCU].[ORDINAL_POSITION] = [KCU2].[ORDINAL_POSITION] WHERE [T].[TABLE_NAME] = 'enterprise' AND [T].[TABLE_TYPE] IN ('BASE table', 'VIEW') AND [T].[TABLE_SCHEMA] NOT IN ('PG_CATALOG', 'INFORMATION_SCHEMA') ORDER BY CASE [TC].[CONSTRAINT_TYPE] WHEN 'PRIMARY KEY' THEN 1 WHEN 'UNIQUE' THEN 2 WHEN 'FOREIGN KEY' THEN 3 WHEN 'CHECK' THEN 4 ELSE 5 END, [TC].[CONSTRAINT_NAME], [KCU].[ORDINAL_POSITION]
            ;SELECT schema_name(tbl.schema_id) AS schema_name, [tbl].[name] AS [table_name], [col].[name] AS [column_name], [idx].[name] AS [index_name], [col].*, [idx].* FROM [sys].[columns] AS [col] LEFT JOIN [sys].[tables] AS [tbl] ON [col].[object_id] = [tbl].[object_id] LEFT JOIN [sys].[index_columns] AS [ic] ON [col].[column_id] = [ic].[column_id] AND [ic].[object_id] = [tbl].[object_id] LEFT JOIN [sys].[indexes] AS [idx] ON [idx].[object_id] = [tbl].[object_id] AND [idx].[index_id] = [ic].[index_id] WHERE [tbl].[name] = 'enterprise' AND ([idx].[name] IS NOT NULL OR [col].[is_identity] = 1 OR [idx].[is_primary_key] = 1)
            ;CREATE INDEX [idx_enterprise_captain] ON [enterprise] ([captain])
            SQL,
            implode("\n;", $logs)
        );
        // phpcs:enable
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
                $this->instance->addIndex(['start_date', 'title']);
            }
        );

        self::assertSqlFormatEquals(
            <<<SQL
            SELECT [c].[ORDINAL_POSITION], [c].[COLUMN_DEFAULT], [c].[IS_NULLABLE], [c].[DATA_TYPE], [c].[CHARACTER_MAXIMUM_LENGTH], [c].[DATETIME_PRECISION], [c].[CHARACTER_OCTET_LENGTH], [c].[NUMERIC_PRECISION], [c].[NUMERIC_SCALE], [c].[COLUMN_NAME], [sc].[is_identity] FROM [INFORMATION_SCHEMA].[COLUMNS] AS [c] LEFT JOIN [sys].[columns] AS [sc] ON [sc].[object_id] = object_id(c.TABLE_NAME) AND [sc].[name] = [c].[COLUMN_NAME] WHERE [c].[TABLE_NAME] = 'enterprise' AND [c].[TABLE_SCHEMA] != 'INFORMATION_SCHEMA';
            SELECT schema_name(tbl.schema_id) AS schema_name, [tbl].[name] AS [table_name], [col].[name] AS [column_name], [idx].[name] AS [index_name], [col].*, [idx].* FROM [sys].[columns] AS [col] LEFT JOIN [sys].[tables] AS [tbl] ON [col].[object_id] = [tbl].[object_id] LEFT JOIN [sys].[index_columns] AS [ic] ON [col].[column_id] = [ic].[column_id] AND [ic].[object_id] = [tbl].[object_id] LEFT JOIN [sys].[indexes] AS [idx] ON [idx].[object_id] = [tbl].[object_id] AND [idx].[index_id] = [ic].[index_id] WHERE [tbl].[name] = 'enterprise' AND ([idx].[name] IS NOT NULL OR [col].[is_identity] = 1 OR [idx].[is_primary_key] = 1);
            CREATE INDEX [idx_enterprise_created] ON [enterprise] ([created]);
            CREATE INDEX [idx_enterprise_start_date_title] ON [enterprise] ([start_date],[title])
            SQL,
            implode(";\n", $logs)
        );

        $this->instance->reset();

        self::assertEquals(
            [
                'pk_enterprise',
                'idx_enterprise_catid_type',
                'idx_enterprise_title',
                'idx_enterprise_start_date_title',
                'idx_enterprise_created',
                'idx_enterprise_captain',
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
        self::assertTrue($this->instance->hasConstraint('pk_enterprise'));
        self::assertFalse($this->instance->hasConstraint('not_exists_constraint'));
    }

    /**
     * @see  TableManager::createSchemaObject
     */
    #[Test]
    public function getSchemaObject(): void
    {
        $schema = $this->instance->createSchemaObject();

        self::assertInstanceOf(Schema::class, $schema);
        self::assertSame('enterprise', $schema->getTable()->getName());
    }

    /**
     * @see  TableManager::hasIndex
     */
    #[Test]
    public function hasIndex(): void
    {
        self::assertTrue($this->instance->hasIndex('idx_enterprise_title'));
        self::assertFalse($this->instance->hasIndex('idx_not_exists'));
    }

    /**
     * @see  TableManager::dropIndex
     */
    #[Test]
    public function dropIndex(): void
    {
        $this->instance->addIndex('type');
        $this->instance->reset();

        self::assertTrue($this->instance->hasIndex('idx_enterprise_type'));

        $this->instance->dropIndex('idx_enterprise_type');
        $this->instance->dropIndex('idx_enterprise_type');

        self::assertFalse($this->instance->reset()->hasIndex('idx_enterprise_type'));
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

        // phpcs:disable
        self::assertSqlFormatEquals(
            <<<SQL
            SELECT [c].[ORDINAL_POSITION], [c].[COLUMN_DEFAULT], [c].[IS_NULLABLE], [c].[DATA_TYPE], [c].[CHARACTER_MAXIMUM_LENGTH], [c].[DATETIME_PRECISION], [c].[CHARACTER_OCTET_LENGTH], [c].[NUMERIC_PRECISION], [c].[NUMERIC_SCALE], [c].[COLUMN_NAME], [sc].[is_identity] FROM [INFORMATION_SCHEMA].[COLUMNS] AS [c] LEFT JOIN [sys].[columns] AS [sc] ON [sc].[object_id] = object_id(c.TABLE_NAME) AND [sc].[name] = [c].[COLUMN_NAME] WHERE [c].[TABLE_NAME] = 'enterprise' AND [c].[TABLE_SCHEMA] != 'INFORMATION_SCHEMA';
            SELECT [T].[TABLE_NAME], [TC].[CONSTRAINT_NAME], [TC].[CONSTRAINT_TYPE], [KCU].[COLUMN_NAME], [CC].[CHECK_CLAUSE], [RC].[MATCH_OPTION], [RC].[UPDATE_RULE], [RC].[DELETE_RULE], [KCU2].[TABLE_SCHEMA] AS [REFERENCED_TABLE_SCHEMA], [KCU2].[TABLE_NAME] AS [REFERENCED_TABLE_NAME], [KCU2].[COLUMN_NAME] AS [REFERENCED_COLUMN_NAME] FROM [INFORMATION_SCHEMA].[TABLES] AS [T] INNER JOIN [INFORMATION_SCHEMA].[TABLE_CONSTRAINTS] AS [TC] ON [T].[TABLE_SCHEMA] = [TC].[TABLE_SCHEMA] AND [T].[TABLE_NAME] = [TC].[TABLE_NAME] LEFT JOIN [INFORMATION_SCHEMA].[KEY_COLUMN_USAGE] AS [KCU] ON [KCU].[TABLE_SCHEMA] = [TC].[TABLE_SCHEMA] AND [KCU].[TABLE_NAME] = [TC].[TABLE_NAME] AND [KCU].[CONSTRAINT_NAME] = [TC].[CONSTRAINT_NAME] LEFT JOIN [INFORMATION_SCHEMA].[CHECK_CONSTRAINTS] AS [CC] ON [CC].[CONSTRAINT_SCHEMA] = [TC].[CONSTRAINT_SCHEMA] AND [CC].[CONSTRAINT_NAME] = [TC].[CONSTRAINT_NAME] LEFT JOIN [INFORMATION_SCHEMA].[REFERENTIAL_CONSTRAINTS] AS [RC] ON [RC].[CONSTRAINT_SCHEMA] = [TC].[CONSTRAINT_SCHEMA] AND [RC].[CONSTRAINT_NAME] = [TC].[CONSTRAINT_NAME] LEFT JOIN [INFORMATION_SCHEMA].[KEY_COLUMN_USAGE] AS [KCU2] ON [RC].[UNIQUE_CONSTRAINT_SCHEMA] = [KCU2].[CONSTRAINT_SCHEMA] AND [RC].[UNIQUE_CONSTRAINT_NAME] = [KCU2].[CONSTRAINT_NAME] AND [KCU].[ORDINAL_POSITION] = [KCU2].[ORDINAL_POSITION] WHERE [T].[TABLE_NAME] = 'enterprise' AND [T].[TABLE_TYPE] IN ('BASE table', 'VIEW') AND [T].[TABLE_SCHEMA] NOT IN ('PG_CATALOG', 'INFORMATION_SCHEMA') ORDER BY CASE [TC].[CONSTRAINT_TYPE] WHEN 'PRIMARY KEY' THEN 1 WHEN 'UNIQUE' THEN 2 WHEN 'FOREIGN KEY' THEN 3 WHEN 'CHECK' THEN 4 ELSE 5 END, [TC].[CONSTRAINT_NAME], [KCU].[ORDINAL_POSITION];
            ALTER TABLE [enterprise]
            ADD CONSTRAINT [ct_enterprise_captain_first_officer] UNIQUE ([captain],[first_officer])
            SQL,
            implode(";\n", $logs)
        );
        // phpcs:enable
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
        $table = self::$db->getTableManager('ww_ss_save', true);

        self::$baseConn->exec("IF OBJECT_ID('ww_ss_save', 'U') IS NOT NULL DROP TABLE ww_ss_save");

        try {
            $table->save(
                static function (Schema $schema) {
                    $schema->primary('id');
                    $schema->varchar('title');
                }
            );

            self::assertTrue($table->exists());
            self::assertTrue($table->hasColumn('title'));

            $table->save(
                static function (Schema $schema) {
                    $schema->primary('id');
                    $schema->varchar('title');
                    $schema->varchar('extra')->nullable(true);
                }
            );

            self::assertTrue($table->hasColumn('extra'));
        } finally {
            self::$baseConn->exec("IF OBJECT_ID('ww_ss_save', 'U') IS NOT NULL DROP TABLE ww_ss_save");
        }
    }

    /**
     * @see  TableManager::addColumn
     */
    #[Test]
    public function addColumn(): void
    {
        $table = self::$db->getTableManager('ww_ss_add_col', true);

        self::$baseConn->exec("IF OBJECT_ID('ww_ss_add_col', 'U') IS NOT NULL DROP TABLE ww_ss_add_col");

        try {
            self::$baseConn->exec('CREATE TABLE ww_ss_add_col (id INT)');

            $table->addColumn('flag', 'int', true);
            $table->reset();

            // Existing column must be ignored
            $table->addColumn('flag', 'int', true);

            self::assertTrue($table->reset()->hasColumn('flag'));
        } finally {
            self::$baseConn->exec("IF OBJECT_ID('ww_ss_add_col', 'U') IS NOT NULL DROP TABLE ww_ss_add_col");
        }
    }

    /**
     * @see  TableManager::truncate
     */
    #[Test]
    public function truncate(): void
    {
        $logs = $this->logQueries(fn() => $this->instance->truncate());

        self::assertEquals('TRUNCATE TABLE [enterprise]', $logs[0]);
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
        $table = clone $this->instance;

        $table->setName('dbo.foo');

        self::assertSame('foo', $table->getName());
        self::assertSame('dbo', $table->schemaName);
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
        $index = $this->instance->getIndex('idx_enterprise_start_date_title');

        self::assertEquals(
            'idx_enterprise_start_date_title',
            $index->indexName,
        );

        self::assertEquals(
            ['title', 'start_date'],
            array_keys($index->getColumns())
        );
    }

    /**
     * @see  TableManager::reset
     */
    #[Test]
    public function reset(): void
    {
        $table = clone $this->instance;

        $columns = $table->getColumns();

        self::assertSame($table, $table->reset());
        self::assertNotSame($columns, $table->getColumns());
        self::assertEquals(array_keys($columns), array_keys($table->getColumns()));
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
        self::assertEquals('datetime2', $column->getDataType());
    }

    /**
     * @see  TableManager::getConstraint
     */
    #[Test]
    public function getConstraint(): void
    {
        $this->instance->addConstraint(
            ['alias'],
            Constraint::TYPE_UNIQUE,
            'const_enterprise_alias',
        );

        $constraint = $this->instance->reset()->getConstraint('const_enterprise_alias');

        self::assertEquals(
            'const_enterprise_alias',
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
                'const_enterprise_alias',
                'idx_enterprise_title',
                'idx_enterprise_start_date_title',
                'idx_enterprise_created',
            ],
            $indexes
        );
    }

    #[Test]
    public function getDatabase(): void
    {
        self::assertSame(
            self::$db->getPlatform()->getCurrentDatabase(),
            $this->instance->getDatabase()->getName()
        );
    }

    #[Test]
    public function getSchema(): void
    {
        $this->instance->schemaName = 'dbo';

        $logs = $this->logQueries(fn() => $this->instance->getColumns());

        self::assertSqlFormatEquals(
            <<<SQL
            SELECT [c].[ORDINAL_POSITION], [c].[COLUMN_DEFAULT], [c].[IS_NULLABLE], [c].[DATA_TYPE], [c].[CHARACTER_MAXIMUM_LENGTH], [c].[DATETIME_PRECISION], [c].[CHARACTER_OCTET_LENGTH], [c].[NUMERIC_PRECISION], [c].[NUMERIC_SCALE], [c].[COLUMN_NAME], [sc].[is_identity] FROM [INFORMATION_SCHEMA].[COLUMNS] AS [c] LEFT JOIN [sys].[columns] AS [sc] ON [sc].[object_id] = object_id(c.TABLE_NAME) AND [sc].[name] = [c].[COLUMN_NAME] WHERE [c].[TABLE_NAME] = 'enterprise' AND [c].[TABLE_SCHEMA] = 'dbo'
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
        $this->instance->dropConstraint('const_enterprise_alias');

        self::assertEquals(
            ['pk_enterprise'],
            array_keys($this->instance->reset()->getConstraints())
        );
    }

    #[Test]
    public function renameColumn(): void
    {
        $this->expectException(\LogicException::class);

        $this->instance->renameColumn('title', 'title_new');
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
