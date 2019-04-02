<?php
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 LYRASOFT Taiwan, Inc.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Query\Test\Postgresql;

use Windwalker\Database\Test\AbstractQueryTestCase;
use Windwalker\Query\Postgresql\PostgresqlGrammar;
use Windwalker\Query\Query;

/**
 * Test class of PostgresqlGrammar
 *
 * @since 2.0
 */
class PostgresqlGrammarTest extends AbstractQueryTestCase
{
    /**
     * Method to test showDatabases().
     *
     * @return void
     *
     * @covers \Windwalker\Query\Postgresql\PostgresqlGrammar::listDatabases
     */
    public function testShowDatabases()
    {
        $expected = "SELECT datname FROM pg_database WHERE a = b AND datistemplate = false;";

        $actual = PostgresqlGrammar::listDatabases('a = b');

        $this->assertEquals($this->format($expected), $this->format($actual));
    }

    /**
     * Method to test createDatabase().
     *
     * @return void
     *
     * @covers \Windwalker\Query\Postgresql\PostgresqlGrammar::createDatabase
     */
    public function testCreateDatabase()
    {
        $expected = "CREATE DATABASE {$this->qn('foo')}";

        $actual = PostgresqlGrammar::createDatabase('foo');

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );

        $expected = "CREATE DATABASE {$this->qn('foo')} ENCODING 'utf8'";

        $actual = PostgresqlGrammar::createDatabase('foo', 'utf8');

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );

        $expected = "CREATE DATABASE {$this->qn('foo')} ENCODING 'utf8' OWNER {$this->qn('bar')}";

        $actual = PostgresqlGrammar::createDatabase('foo', 'utf8', 'bar');

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );
    }

    /**
     * Method to test dropDatabase().
     *
     * @return void
     *
     * @covers \Windwalker\Query\Postgresql\PostgresqlGrammar::dropDatabase
     */
    public function testDropDatabase()
    {
        $expected = "DROP DATABASE {$this->qn('foo')}";

        $actual = PostgresqlGrammar::dropDatabase('foo');

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );

        $expected = "DROP DATABASE IF EXISTS {$this->qn('foo')}";

        $actual = PostgresqlGrammar::dropDatabase('foo', true);

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );
    }

    /**
     * Method to test showTableColumns().
     *
     * @return void
     *
     * @covers \Windwalker\Query\Postgresql\PostgresqlGrammar::showTableColumns
     */
    public function testShowTableColumns()
    {
        $expected = <<<SQL
SELECT attr.attname AS "column_name",
    pg_catalog.format_type(attr.atttypid, attr.atttypmod) AS "column_type",
    CASE WHEN attr.attnotnull IS TRUE THEN 'NO' ELSE 'YES' END AS "Null",
    attrdef.adsrc AS "Default",
    pg_catalog.col_description(attr.attrelid, attr.attnum) AS "Comment"
FROM pg_catalog.pg_attribute AS attr
    LEFT JOIN pg_catalog.pg_class       AS class   ON class.oid = attr.attrelid
    LEFT JOIN pg_catalog.pg_type        AS typ     ON typ.oid = attr.atttypid
    LEFT JOIN pg_catalog.pg_attrdef     AS attrdef ON attr.attrelid = attrdef.adrelid AND attr.attnum = attrdef.adnum
WHERE attr.attrelid = (SELECT oid FROM pg_catalog.pg_class WHERE relname='foo'
    AND relnamespace = (SELECT oid FROM pg_catalog.pg_namespace WHERE
    nspname = 'public')) AND attr.attnum > 0 AND NOT attr.attisdropped
ORDER BY attr.attnum
SQL;

        $actual = PostgresqlGrammar::showTableColumns('foo');

        $this->assertEquals(
            \SqlFormatter::format($expected, false),
            \SqlFormatter::format($actual, false)
        );
    }

    /**
     * Method to test showDbTables().
     *
     * @return void
     *
     * @covers \Windwalker\Query\Postgresql\PostgresqlGrammar::showDbTables
     */
    public function testShowDbTables()
    {
        $expected = <<<SQL
SELECT table_name AS "Name"
FROM information_schema.tables
WHERE table_type = 'BASE TABLE'
  AND table_schema NOT IN ('pg_catalog', 'information_schema')
ORDER BY
  table_name ASC
SQL;

        $actual = PostgresqlGrammar::showDbTables('foo');

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );
    }

    /**
     * Method to test createTable().
     *
     * @return void
     *
     * @covers \Windwalker\Query\Postgresql\PostgresqlGrammar::createTable
     */
    public function testCreateTable()
    {
        $expected = <<<SQL
CREATE TABLE IF NOT EXISTS {$this->qn('foo')} (
  {$this->qn('id')}    serial NOT NULL,
  {$this->qn('name')}  varchar(255) NOT NULL,
  {$this->qn('email')} varchar(255) NOT NULL,
  PRIMARY KEY ({$this->qn('id')})
) INHERITS ({$this->qn('bar')}) TABLESPACE {$this->qn('tablespace')};

CREATE INDEX {$this->qn('idx_alias')} ON {$this->qn('foo')} ({$this->qn('email')})
SQL;

        $columns = [
            'id' => 'serial NOT NULL',
            'name' => ['varchar(255)', 'NOT NULL'],
            'email' => "varchar(255) NOT NULL",
        ];

        $keys = [
            ['type' => 'INDEX', 'name' => 'idx_alias', 'columns' => 'email'],
        ];

        $actual = PostgresqlGrammar::createTable('foo', $columns, 'id', $keys, 'bar', true, 'tablespace');

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );

        $expected = <<<SQL
CREATE TABLE {$this->qn('foo')} (
  {$this->qn('id')} int(11) NOT NULL,
  {$this->qn('name')} varchar(255) NOT NULL,
  {$this->qn('email')} varchar(255) NOT NULL,
  PRIMARY KEY ({$this->qn('id')}, {$this->qn('email')})
);
CREATE UNIQUE INDEX {$this->qn('idx_alias')} ON {$this->qn('foo')} ({$this->qn('email')}, {$this->qn('id')})
SQL;

        $columns = [
            'id' => 'int(11) NOT NULL',
            'name' => ['varchar(255)', 'NOT NULL'],
            'email' => "varchar(255) NOT NULL",
        ];

        $keys = [
            ['type' => 'UNIQUE INDEX', 'name' => 'idx_alias', 'columns' => ['email', 'id']],
        ];

        $actual = PostgresqlGrammar::createTable('foo', $columns, ['id', 'email'], $keys, null, false, null);

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );
    }

    /**
     * Method to test dropTable().
     *
     * @return void
     *
     * @covers \Windwalker\Query\Postgresql\PostgresqlGrammar::dropTable
     */
    public function testDropTable()
    {
        $expected = "DROP TABLE {$this->qn('foo')}";

        $actual = PostgresqlGrammar::dropTable('foo');

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );

        $expected = "DROP TABLE IF EXISTS {$this->qn('foo')}";

        $actual = PostgresqlGrammar::dropTable('foo', true);

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );
    }

    /**
     * Method to test alterColumn().
     *
     * @return void
     *
     * @covers \Windwalker\Query\Postgresql\PostgresqlGrammar::alterColumn
     */
    public function testAlterColumn()
    {
        $expected = "ALTER TABLE {$this->qn('foo')} ADD {$this->qn('bar')} int(11) NOT NULL SET DEFAULT '1'";

        $actual = PostgresqlGrammar::alterColumn('ADD', 'foo', 'bar', 'int(11)', true, '1');

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );

        $expected = "ALTER TABLE {$this->qn('foo')} RENAME {$this->qn('bar')} TO {$this->qn('yoo')}";

        $actual = PostgresqlGrammar::alterColumn('RENAME', 'foo', ['bar', 'yoo'], null, false, null);

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );
    }

    /**
     * Method to test addColumn().
     *
     * @return void
     *
     * @covers \Windwalker\Query\Postgresql\PostgresqlGrammar::addColumn
     */
    public function testAddColumn()
    {
        $expected = "ALTER TABLE {$this->qn('foo')} ADD {$this->qn('bar')} int(11) NOT NULL DEFAULT '1'";

        $actual = PostgresqlGrammar::addColumn('foo', 'bar', 'int(11)', false, '1');

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );
    }

    /**
     * Method to test renameColumn().
     *
     * @return void
     *
     * @covers \Windwalker\Query\Postgresql\PostgresqlGrammar::renameColumn
     */
    public function testRenameColumn()
    {
        $expected = "ALTER TABLE {$this->qn('foo')} RENAME {$this->qn('bar')} TO {$this->qn('yoo')}";

        $actual = PostgresqlGrammar::renameColumn('foo', 'bar', 'yoo');

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );
    }

    /**
     * Method to test dropColumn().
     *
     * @return void
     *
     * @covers \Windwalker\Query\Postgresql\PostgresqlGrammar::dropColumn
     */
    public function testDropColumn()
    {
        $expected = "ALTER TABLE {$this->qn('foo')} DROP {$this->qn('bar')}";

        $actual = PostgresqlGrammar::dropColumn('foo', 'bar');

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );
    }

    /**
     * Method to test addIndex().
     *
     * @return void
     *
     * @covers \Windwalker\Query\Postgresql\PostgresqlGrammar::addIndex
     */
    public function testAddIndex()
    {
        $expected = "CREATE INDEX {$this->qn('idx_alias')} ON {$this->qn('foo')} ({$this->qn('alias')}, {$this->qn('name')})";

        $actual = PostgresqlGrammar::addIndex('foo', 'INDEX', ['alias', 'name'], 'idx_alias');

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );

        $expected = "CREATE INDEX {$this->qn('idx_alias')} ON {$this->qn('foo')} ({$this->qn('alias')})";

        $actual = PostgresqlGrammar::addIndex('foo', 'INDEX', 'alias', 'idx_alias');

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );
    }

    /**
     * Method to test buildIndexDeclare().
     *
     * @return void
     *
     * @covers \Windwalker\Query\Postgresql\PostgresqlGrammar::buildIndexDeclare
     */
    public function testBuildIndexDeclare()
    {
        $expected = "{$this->qn('idx_alias')} ({$this->qn('alias')})";

        $actual = PostgresqlGrammar::buildIndexDeclare('idx_alias', 'alias', null);

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );

        $expected = "{$this->qn('idx_alias')} ON {$this->qn('foo')} ({$this->qn('alias')}, {$this->qn('name')})";

        $actual = PostgresqlGrammar::buildIndexDeclare('idx_alias', ['alias', 'name'], 'foo');

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );
    }

    /**
     * Method to test dropIndex().
     *
     * @return void
     *
     * @covers \Windwalker\Query\Postgresql\PostgresqlGrammar::dropIndex
     */
    public function testDropIndex()
    {
        $expected = "DROP INDEX {$this->qn('bar')}";

        $actual = PostgresqlGrammar::dropIndex('bar');

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );

        $expected = "DROP INDEX CONCURRENTLY IF EXISTS {$this->qn('bar')}";

        $actual = PostgresqlGrammar::dropIndex('bar', true, true);

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );
    }

    /**
     * Method to test build().
     *
     * @return void
     *
     * @covers \Windwalker\Query\Postgresql\PostgresqlGrammar::build
     */
    public function testBuild()
    {
        $expected = "FLOWER SAKURA SUNFLOWER OLIVE";

        $actual = PostgresqlGrammar::build('FLOWER', 'SAKURA', 'SUNFLOWER', 'OLIVE');

        $this->assertEquals(
            $this->format($expected),
            $this->format($actual)
        );
    }

    /**
     * Method to test getQuery().
     *
     * @return void
     *
     * @covers \Windwalker\Query\Postgresql\PostgresqlGrammar::getQuery
     */
    public function testGetQuery()
    {
        $this->assertInstanceOf('Windwalker\\Query\\Postgresql\\PostgresqlQuery', PostgresqlGrammar::getQuery());

        $this->assertSame(PostgresqlGrammar::getQuery(), PostgresqlGrammar::getQuery());

        $this->assertNotSame(PostgresqlGrammar::getQuery(), PostgresqlGrammar::getQuery(true));
    }
}
