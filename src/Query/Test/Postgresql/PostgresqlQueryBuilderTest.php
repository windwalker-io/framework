<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Query\Test\Postgresql;

use Windwalker\Query\Postgresql\PostgresqlQueryBuilder;
use Windwalker\Query\Test\AbstractQueryBuilderTestCase;

/**
 * Test class of PostgresqlQueryBuilder
 *
 * @since 2.0
 */
class PostgresqlQueryBuilderTest extends AbstractQueryBuilderTestCase
{
	/**
	 * Property qn.
	 *
	 * @var  string
	 */
	protected $qn = '"';

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

		$this->assertEquals(\SqlFormatter::compress($expected), \SqlFormatter::compress($actual));
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
			\SqlFormatter::compress($expected),
			\SqlFormatter::compress($actual)
		);

		$expected = "CREATE DATABASE {$this->qn('foo')} ENCODING 'utf8'";

		$actual = PostgresqlQueryBuilder::createDatabase('foo', 'utf8');

		$this->assertEquals(
			\SqlFormatter::compress($expected),
			\SqlFormatter::compress($actual)
		);

		$expected = "CREATE DATABASE {$this->qn('foo')} ENCODING 'utf8' OWNER {$this->qn('bar')}";

		$actual = PostgresqlQueryBuilder::createDatabase('foo', 'utf8', 'bar');

		$this->assertEquals(
			\SqlFormatter::compress($expected),
			\SqlFormatter::compress($actual)
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
			\SqlFormatter::compress($expected),
			\SqlFormatter::compress($actual)
		);

		$expected = "DROP DATABASE IF EXISTS {$this->qn('foo')}";

		$actual = PostgresqlQueryBuilder::dropDatabase('foo', true);

		$this->assertEquals(
			\SqlFormatter::compress($expected),
			\SqlFormatter::compress($actual)
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
SELECT attr.attname AS "Field",pg_catalog.format_type(attr.atttypid, attr.atttypmod) AS "Type",CASE WHEN attr.attnotnull IS TRUE THEN 'NO' ELSE 'YES' END AS "Null",attrdef.adsrc AS "Default",dsc.description AS "Comments"
FROM pg_catalog.pg_attribute AS attr
LEFT JOIN pg_catalog.pg_class AS class ON class.oid = attr.attrelid
LEFT JOIN pg_catalog.pg_type AS typ ON typ.oid = attr.atttypid
LEFT JOIN pg_catalog.pg_attrdef AS attrdef ON attr.attrelid = attrdef.adrelid AND attr.attnum = attrdef.adnum
LEFT JOIN pg_catalog.pg_description AS dsc ON dsc.classoid = class.oid
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
		$expected = "SHOW TABLE STATUS FROM {$this->qn('foo')}";

		$actual = PostgresqlQueryBuilder::showDbTables('foo');

		$this->assertEquals(
			\SqlFormatter::compress($expected),
			\SqlFormatter::compress($actual)
		);

		$expected = "SHOW TABLE STATUS FROM {$this->qn('foo')} WHERE a = b";

		$actual = PostgresqlQueryBuilder::showDbTables('foo', 'a = b');

		$this->assertEquals(
			\SqlFormatter::compress($expected),
			\SqlFormatter::compress($actual)
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
  {$this->qn('id')} int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  {$this->qn('name')} varchar(255) NOT NULL COMMENT 'Member Name',
  {$this->qn('email')} varchar(255) NOT NULL COMMENT 'Member email',
  PRIMARY KEY ({$this->qn('id')}),
  KEY {$this->qn('idx_alias')} ({$this->qn('email')})
) ENGINE=InnoDB AUTO_INCREMENT=415 DEFAULT CHARSET=utf8
SQL;

		$columns = array(
			'id' => 'int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT \'Primary Key\'',
			'name' => array('varchar(255)', 'NOT NULL', 'COMMENT \'Member Name\''),
			'email' => "varchar(255) NOT NULL COMMENT 'Member email'"
		);

		$keys = array(
			array('type' => 'KEY', 'name' => 'idx_alias', 'columns' => 'email')
		);

		$actual = PostgresqlQueryBuilder::createTable('foo', $columns, 'id', $keys, 415, true, 'InnoDB');

		$this->assertEquals(
			\SqlFormatter::compress($expected),
			\SqlFormatter::compress($actual)
		);

		$expected = <<<SQL
CREATE TABLE {$this->qn('foo')} (
  {$this->qn('id')} int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  {$this->qn('name')} varchar(255) NOT NULL COMMENT 'Member Name',
  {$this->qn('email')} varchar(255) NOT NULL COMMENT 'Member email',
  PRIMARY KEY ({$this->qn('id')}, {$this->qn('email')}),
  UNIQUE KEY {$this->qn('idx_alias')} ({$this->qn('email')}, {$this->qn('id')})
) ENGINE=InnoDB AUTO_INCREMENT=415 DEFAULT CHARSET=utf8
SQL;

		$columns = array(
			'id' => 'int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT \'Primary Key\'',
			'name' => array('varchar(255)', 'NOT NULL', 'COMMENT \'Member Name\''),
			'email' => "varchar(255) NOT NULL COMMENT 'Member email'"
		);

		$keys = array(
			array('type' => 'UNIQUE KEY', 'name' => 'idx_alias', 'columns' => array('email', 'id'))
		);

		$actual = PostgresqlQueryBuilder::createTable('foo', $columns, array('id', 'email'), $keys, 415, false, 'InnoDB');

		$this->assertEquals(
			\SqlFormatter::compress($expected),
			\SqlFormatter::compress($actual)
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
			\SqlFormatter::compress($expected),
			\SqlFormatter::compress($actual)
		);

		$expected = "DROP TABLE IF EXISTS {$this->qn('foo')}";

		$actual = PostgresqlQueryBuilder::dropTable('foo', true);

		$this->assertEquals(
			\SqlFormatter::compress($expected),
			\SqlFormatter::compress($actual)
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
		$expected = "ALTER TABLE {$this->qn('foo')} MODIFY {$this->qn('bar')} int(11) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Test' FIRST";

		$actual = PostgresqlQueryBuilder::alterColumn('MODIFY', 'foo', 'bar', 'int(11)', true, true, '1', 'FIRST', 'Test');

		$this->assertEquals(
			\SqlFormatter::compress($expected),
			\SqlFormatter::compress($actual)
		);

		$expected = "ALTER TABLE {$this->qn('foo')} CHANGE {$this->qn('bar')} {$this->qn('yoo')} text AFTER {$this->qn('id')}";

		$actual = PostgresqlQueryBuilder::alterColumn('CHANGE', 'foo', array('bar', 'yoo'), 'text', false, false, null, 'AFTER id', null);

		$this->assertEquals(
			\SqlFormatter::compress($expected),
			\SqlFormatter::compress($actual)
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
		$expected = "ALTER TABLE {$this->qn('foo')} ADD {$this->qn('bar')} int(11) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Test' FIRST";

		$actual = PostgresqlQueryBuilder::addColumn('foo', 'bar', 'int(11)', true, true, '1', 'FIRST', 'Test');

		$this->assertEquals(
			\SqlFormatter::compress($expected),
			\SqlFormatter::compress($actual)
		);
	}

	/**
	 * Method to test changeColumn().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Postgresql\PostgresqlQueryBuilder::changeColumn
	 */
	public function testChangeColumn()
	{
		$expected = "ALTER TABLE {$this->qn('foo')} CHANGE {$this->qn('bar')} {$this->qn('yoo')} int(11) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Test' FIRST";

		$actual = PostgresqlQueryBuilder::changeColumn('foo', 'bar', 'yoo', 'int(11)', true, true, '1', 'FIRST', 'Test');

		$this->assertEquals(
			\SqlFormatter::compress($expected),
			\SqlFormatter::compress($actual)
		);
	}

	/**
	 * Method to test modifyColumn().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Postgresql\PostgresqlQueryBuilder::modifyColumn
	 */
	public function testModifyColumn()
	{
		$expected = "ALTER TABLE {$this->qn('foo')} MODIFY {$this->qn('bar')} int(11) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Test' FIRST";

		$actual = PostgresqlQueryBuilder::modifyColumn('foo', 'bar', 'int(11)', true, true, '1', 'FIRST', 'Test');

		$this->assertEquals(
			\SqlFormatter::compress($expected),
			\SqlFormatter::compress($actual)
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
			\SqlFormatter::compress($expected),
			\SqlFormatter::compress($actual)
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
		$expected = "ALTER TABLE {$this->qn('foo')} ADD KEY {$this->qn('idx_alias')} ({$this->qn('alias')}, {$this->qn('name')}) COMMENT 'Test Index'";

		$actual = PostgresqlQueryBuilder::addIndex('foo', 'KEY', 'idx_alias', array('alias', 'name'), 'Test Index');

		$this->assertEquals(
			\SqlFormatter::compress($expected),
			\SqlFormatter::compress($actual)
		);

		$expected = "ALTER TABLE {$this->qn('foo')} ADD KEY {$this->qn('idx_alias')} ({$this->qn('alias')}) COMMENT 'Test Index'";

		$actual = PostgresqlQueryBuilder::addIndex('foo', 'KEY', 'idx_alias', 'alias', 'Test Index');

		$this->assertEquals(
			\SqlFormatter::compress($expected),
			\SqlFormatter::compress($actual)
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

		$actual = PostgresqlQueryBuilder::buildIndexDeclare('idx_alias', 'alias');

		$this->assertEquals(
			\SqlFormatter::compress($expected),
			\SqlFormatter::compress($actual)
		);

		$expected = "{$this->qn('idx_alias')} ({$this->qn('alias')}, {$this->qn('name')})";

		$actual = PostgresqlQueryBuilder::buildIndexDeclare('idx_alias', array('alias', 'name'));

		$this->assertEquals(
			\SqlFormatter::compress($expected),
			\SqlFormatter::compress($actual)
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
		$expected = "ALTER TABLE {$this->qn('foo')} DROP INDEX {$this->qn('bar')}";

		$actual = PostgresqlQueryBuilder::dropIndex('foo', 'INDEX', 'bar');

		$this->assertEquals(
			\SqlFormatter::compress($expected),
			\SqlFormatter::compress($actual)
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
			\SqlFormatter::compress($expected),
			\SqlFormatter::compress($actual)
		);
	}

	/**
	 * Method to test replace().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Postgresql\PostgresqlQueryBuilder::replace
	 */
	public function testReplace()
	{
		$expected = "REPLACE INTO {$this->qn('foo')} (a,b) VALUES (c, d, e), (f, g, h)";

		$actual = PostgresqlQueryBuilder::replace('foo', array('a', 'b'), array('c, d, e', 'f, g, h'));

		$this->assertEquals(
			\SqlFormatter::compress($expected),
			\SqlFormatter::compress($actual)
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
