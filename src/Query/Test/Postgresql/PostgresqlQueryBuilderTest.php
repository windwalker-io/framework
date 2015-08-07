<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Query\Test\Postgresql;

use Windwalker\Query\Postgresql\PostgresqlQueryBuilder;
use Windwalker\Database\Test\AbstractQueryTestCase;

/**
 * Test class of PostgresqlQueryBuilder
 *
 * @since 2.0
 */
class PostgresqlQueryBuilderTest extends AbstractQueryTestCase
{
	/**
	 * Method to test showDatabases().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Postgresql\PostgresqlQueryBuilder::listDatabases
	 */
	public function testShowDatabases()
	{
		$expected = "SELECT datname FROM pg_database WHERE a = b AND datistemplate = false;";

		$actual = PostgresqlQueryBuilder::listDatabases('a = b');

		$this->assertEquals($this->format($expected), $this->format($actual));
	}

	/**
	 * Method to test createDatabase().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Postgresql\PostgresqlQueryBuilder::createDatabase
	 */
	public function testCreateDatabase()
	{
		$expected = "CREATE DATABASE {$this->qn('foo')}";

		$actual = PostgresqlQueryBuilder::createDatabase('foo');

		$this->assertEquals(
			$this->format($expected),
			$this->format($actual)
		);

		$expected = "CREATE DATABASE {$this->qn('foo')} ENCODING 'utf8'";

		$actual = PostgresqlQueryBuilder::createDatabase('foo', 'utf8');

		$this->assertEquals(
			$this->format($expected),
			$this->format($actual)
		);

		$expected = "CREATE DATABASE {$this->qn('foo')} ENCODING 'utf8' OWNER {$this->qn('bar')}";

		$actual = PostgresqlQueryBuilder::createDatabase('foo', 'utf8', 'bar');

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
	 * @covers Windwalker\Query\Postgresql\PostgresqlQueryBuilder::dropDatabase
	 */
	public function testDropDatabase()
	{
		$expected = "DROP DATABASE {$this->qn('foo')}";

		$actual = PostgresqlQueryBuilder::dropDatabase('foo');

		$this->assertEquals(
			$this->format($expected),
			$this->format($actual)
		);

		$expected = "DROP DATABASE IF EXISTS {$this->qn('foo')}";

		$actual = PostgresqlQueryBuilder::dropDatabase('foo', true);

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
	 * @covers Windwalker\Query\Postgresql\PostgresqlQueryBuilder::showTableColumns
	 */
	public function testShowTableColumns()
	{
		$expected = <<<SQL
SELECT attr.attname AS "column_name",
	pg_catalog.format_type(attr.atttypid, attr.atttypmod) AS "column_type",
	CASE WHEN attr.attnotnull IS TRUE THEN 'NO' ELSE 'YES' END AS "Null",
	attrdef.adsrc AS "Default",dsc.description AS "comments"
FROM pg_catalog.pg_attribute AS attr
	LEFT JOIN pg_catalog.pg_class       AS class   ON class.oid = attr.attrelid
	LEFT JOIN pg_catalog.pg_type        AS typ     ON typ.oid = attr.atttypid
	LEFT JOIN pg_catalog.pg_attrdef     AS attrdef ON attr.attrelid = attrdef.adrelid AND attr.attnum = attrdef.adnum
	LEFT JOIN pg_catalog.pg_description AS dsc     ON dsc.classoid = class.oid
WHERE attr.attrelid = (SELECT oid FROM pg_catalog.pg_class WHERE relname='foo'
	AND relnamespace = (SELECT oid FROM pg_catalog.pg_namespace WHERE
	nspname = 'public')) AND attr.attnum > 0 AND NOT attr.attisdropped
ORDER BY attr.attnum
SQL;

		$actual = PostgresqlQueryBuilder::showTableColumns('foo');

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
	 * @covers Windwalker\Query\Postgresql\PostgresqlQueryBuilder::showDbTables
	 */
	public function testShowDbTables()
	{
		$expected = <<<SQL
SELECT table_name
FROM information_schema.tables
WHERE table_type = 'BASE TABLE'
  AND table_schema NOT IN ('pg_catalog', 'information_schema')
ORDER BY
  table_name ASC
SQL;

		$actual = PostgresqlQueryBuilder::showDbTables('foo');

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
	 * @covers Windwalker\Query\Postgresql\PostgresqlQueryBuilder::createTable
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

CREATE INDEX {$this->qn('idx_alias')} ON {$this->qn('foo')} ({$this->qn('email')});
SQL;

		$columns = array(
			'id' => 'serial NOT NULL',
			'name' => array('varchar(255)', 'NOT NULL'),
			'email' => "varchar(255) NOT NULL"
		);

		$keys = array(
			array('type' => 'INDEX', 'name' => 'idx_alias', 'columns' => 'email')
		);

		$actual = PostgresqlQueryBuilder::createTable('foo', $columns, 'id', $keys, 'bar', true, 'tablespace');

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
CREATE UNIQUE INDEX {$this->qn('idx_alias')} ON {$this->qn('foo')} ({$this->qn('email')}, {$this->qn('id')});
SQL;

		$columns = array(
			'id' => 'int(11) NOT NULL',
			'name' => array('varchar(255)', 'NOT NULL'),
			'email' => "varchar(255) NOT NULL"
		);

		$keys = array(
			array('type' => 'UNIQUE INDEX', 'name' => 'idx_alias', 'columns' => array('email', 'id'))
		);

		$actual = PostgresqlQueryBuilder::createTable('foo', $columns, array('id', 'email'), $keys, null, false, null);

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
	 * @covers Windwalker\Query\Postgresql\PostgresqlQueryBuilder::dropTable
	 */
	public function testDropTable()
	{
		$expected = "DROP TABLE {$this->qn('foo')}";

		$actual = PostgresqlQueryBuilder::dropTable('foo');

		$this->assertEquals(
			$this->format($expected),
			$this->format($actual)
		);

		$expected = "DROP TABLE IF EXISTS {$this->qn('foo')}";

		$actual = PostgresqlQueryBuilder::dropTable('foo', true);

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
	 * @covers Windwalker\Query\Postgresql\PostgresqlQueryBuilder::alterColumn
	 */
	public function testAlterColumn()
	{
		$expected = "ALTER TABLE {$this->qn('foo')} ADD {$this->qn('bar')} int(11) NOT NULL DEFAULT '1'";

		$actual = PostgresqlQueryBuilder::alterColumn('ADD', 'foo', 'bar', 'int(11)', true, '1');

		$this->assertEquals(
			$this->format($expected),
			$this->format($actual)
		);

		$expected = "ALTER TABLE {$this->qn('foo')} RENAME {$this->qn('bar')} TO {$this->qn('yoo')}";

		$actual = PostgresqlQueryBuilder::alterColumn('RENAME', 'foo', array('bar', 'yoo'), null, false, null);

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
	 * @covers Windwalker\Query\Postgresql\PostgresqlQueryBuilder::addColumn
	 */
	public function testAddColumn()
	{
		$expected = "ALTER TABLE {$this->qn('foo')} ADD {$this->qn('bar')} int(11) NOT NULL DEFAULT '1'";

		$actual = PostgresqlQueryBuilder::addColumn('foo', 'bar', 'int(11)', true, '1');

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
	 * @covers Windwalker\Query\Postgresql\PostgresqlQueryBuilder::renameColumn
	 */
	public function testRenameColumn()
	{
		$expected = "ALTER TABLE {$this->qn('foo')} RENAME {$this->qn('bar')} TO {$this->qn('yoo')} int(11) NOT NULL DEFAULT '1'";

		$actual = PostgresqlQueryBuilder::renameColumn('foo', 'bar', 'yoo', 'int(11)', true, '1');

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
	 * @covers Windwalker\Query\Postgresql\PostgresqlQueryBuilder::dropColumn
	 */
	public function testDropColumn()
	{
		$expected = "ALTER TABLE {$this->qn('foo')} DROP {$this->qn('bar')}";

		$actual = PostgresqlQueryBuilder::dropColumn('foo', 'bar');

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
	 * @covers Windwalker\Query\Postgresql\PostgresqlQueryBuilder::addIndex
	 */
	public function testAddIndex()
	{
		$expected = "CREATE INDEX {$this->qn('idx_alias')} ON {$this->qn('foo')} ({$this->qn('alias')}, {$this->qn('name')})";

		$actual = PostgresqlQueryBuilder::addIndex('foo', 'INDEX', 'idx_alias', array('alias', 'name'));

		$this->assertEquals(
			$this->format($expected),
			$this->format($actual)
		);

		$expected = "CREATE INDEX {$this->qn('idx_alias')} ON {$this->qn('foo')} ({$this->qn('alias')})";

		$actual = PostgresqlQueryBuilder::addIndex('foo', 'INDEX', 'idx_alias', 'alias');

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
	 * @covers Windwalker\Query\Postgresql\PostgresqlQueryBuilder::buildIndexDeclare
	 */
	public function testBuildIndexDeclare()
	{
		$expected = "{$this->qn('idx_alias')} ({$this->qn('alias')})";

		$actual = PostgresqlQueryBuilder::buildIndexDeclare('idx_alias', 'alias', null);

		$this->assertEquals(
			$this->format($expected),
			$this->format($actual)
		);

		$expected = "{$this->qn('idx_alias')} ON {$this->qn('foo')} ({$this->qn('alias')}, {$this->qn('name')})";

		$actual = PostgresqlQueryBuilder::buildIndexDeclare('idx_alias', array('alias', 'name'), 'foo');

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
	 * @covers Windwalker\Query\Postgresql\PostgresqlQueryBuilder::dropIndex
	 */
	public function testDropIndex()
	{
		$expected = "DROP INDEX {$this->qn('bar')}";

		$actual = PostgresqlQueryBuilder::dropIndex('bar');

		$this->assertEquals(
			$this->format($expected),
			$this->format($actual)
		);

		$expected = "DROP INDEX CONCURRENTLY IF EXISTS {$this->qn('bar')}";

		$actual = PostgresqlQueryBuilder::dropIndex('bar', true, true);

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
	 * @covers Windwalker\Query\Postgresql\PostgresqlQueryBuilder::build
	 */
	public function testBuild()
	{
		$expected = "FLOWER SAKURA SUNFLOWER OLIVE";

		$actual = PostgresqlQueryBuilder::build('FLOWER', 'SAKURA', 'SUNFLOWER', 'OLIVE');

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
	 * @covers Windwalker\Query\Postgresql\PostgresqlQueryBuilder::getQuery
	 */
	public function testGetQuery()
	{
		$this->assertInstanceOf('Windwalker\\Query\\Postgresql\\PostgresqlQuery', PostgresqlQueryBuilder::getQuery());

		$this->assertSame(PostgresqlQueryBuilder::getQuery(), PostgresqlQueryBuilder::getQuery());

		$this->assertNotSame(PostgresqlQueryBuilder::getQuery(), PostgresqlQueryBuilder::getQuery(true));
	}
}
