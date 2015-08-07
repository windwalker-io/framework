<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Query\Test\Mysql;

use Windwalker\Query\Mysql\MysqlQueryBuilder;
use Windwalker\Database\Test\AbstractQueryTestCase;

/**
 * Test class of MysqlQueryBuilder
 *
 * @since 2.0
 */
class MysqlQueryBuilderTest extends AbstractQueryTestCase
{
	/**
	 * Property quote.
	 *
	 * @var  array
	 */
	protected static $quote = '`';

	/**
	 * Method to test showDatabases().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Mysql\MysqlQueryBuilder::listDatabases
	 */
	public function testShowDatabases()
	{
		$expected = "SHOW DATABASES WHERE a = b";

		$actual = MysqlQueryBuilder::listDatabases('a = b');

		$this->assertEquals($this->format($expected), $this->format($actual));
	}

	/**
	 * Method to test createDatabase().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Mysql\MysqlQueryBuilder::createDatabase
	 */
	public function testCreateDatabase()
	{
		$expected = "CREATE DATABASE {$this->qn('foo')}";

		$actual = MysqlQueryBuilder::createDatabase('foo');

		$this->assertEquals(
			$this->format($expected),
			$this->format($actual)
		);

		$expected = "CREATE DATABASE IF NOT EXISTS {$this->qn('foo')}";

		$actual = MysqlQueryBuilder::createDatabase('foo', true);

		$this->assertEquals(
			$this->format($expected),
			$this->format($actual)
		);

		$expected = "CREATE DATABASE IF NOT EXISTS {$this->qn('foo')} CHARACTER SET='utf8' COLLATE='bar'";

		$actual = MysqlQueryBuilder::createDatabase('foo', true, 'utf8', 'bar');

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
	 * @covers Windwalker\Query\Mysql\MysqlQueryBuilder::dropDatabase
	 */
	public function testDropDatabase()
	{
		$expected = "DROP DATABASE {$this->qn('foo')}";

		$actual = MysqlQueryBuilder::dropDatabase('foo');

		$this->assertEquals(
			$this->format($expected),
			$this->format($actual)
		);

		$expected = "DROP DATABASE IF EXISTS {$this->qn('foo')}";

		$actual = MysqlQueryBuilder::dropDatabase('foo', true);

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
	 * @covers Windwalker\Query\Mysql\MysqlQueryBuilder::showTableColumns
	 */
	public function testShowTableColumns()
	{
		$expected = "SHOW COLUMNS FROM {$this->qn('foo')}";

		$actual = MysqlQueryBuilder::showTableColumns('foo');

		$this->assertEquals(
			$this->format($expected),
			$this->format($actual)
		);

		$expected = "SHOW FULL COLUMNS FROM {$this->qn('foo')} WHERE a = b";

		$actual = MysqlQueryBuilder::showTableColumns('foo', true, 'a = b');

		$this->assertEquals(
			$this->format($expected),
			$this->format($actual)
		);
	}

	/**
	 * Method to test showDbTables().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Mysql\MysqlQueryBuilder::showDbTables
	 */
	public function testShowDbTables()
	{
		$expected = "SHOW TABLE STATUS FROM {$this->qn('foo')}";

		$actual = MysqlQueryBuilder::showDbTables('foo');

		$this->assertEquals(
			$this->format($expected),
			$this->format($actual)
		);

		$expected = "SHOW TABLE STATUS FROM {$this->qn('foo')} WHERE a = b";

		$actual = MysqlQueryBuilder::showDbTables('foo', 'a = b');

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
	 * @covers Windwalker\Query\Mysql\MysqlQueryBuilder::createTable
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

		$actual = MysqlQueryBuilder::createTable('foo', $columns, 'id', $keys, 415, true, 'InnoDB');

		$this->assertEquals(
			$this->format($expected),
			$this->format($actual)
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

		$actual = MysqlQueryBuilder::createTable('foo', $columns, array('id', 'email'), $keys, 415, false, 'InnoDB');

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
	 * @covers Windwalker\Query\Mysql\MysqlQueryBuilder::dropTable
	 */
	public function testDropTable()
	{
		$expected = "DROP TABLE {$this->qn('foo')}";

		$actual = MysqlQueryBuilder::dropTable('foo');

		$this->assertEquals(
			$this->format($expected),
			$this->format($actual)
		);

		$expected = "DROP TABLE IF EXISTS {$this->qn('foo')}";

		$actual = MysqlQueryBuilder::dropTable('foo', true);

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
	 * @covers Windwalker\Query\Mysql\MysqlQueryBuilder::alterColumn
	 */
	public function testAlterColumn()
	{
		$expected = "ALTER TABLE {$this->qn('foo')} MODIFY {$this->qn('bar')} int(11) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Test' FIRST";

		$actual = MysqlQueryBuilder::alterColumn('MODIFY', 'foo', 'bar', 'int(11)', false, false, '1', 'FIRST', 'Test');

		$this->assertEquals(
			$this->format($expected),
			$this->format($actual)
		);

		$expected = "ALTER TABLE {$this->qn('foo')} CHANGE {$this->qn('bar')} {$this->qn('yoo')} text AFTER {$this->qn('id')}";

		$actual = MysqlQueryBuilder::alterColumn('CHANGE', 'foo', array('bar', 'yoo'), 'text', true, true, null, 'AFTER id', null);

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
	 * @covers Windwalker\Query\Mysql\MysqlQueryBuilder::addColumn
	 */
	public function testAddColumn()
	{
		$expected = "ALTER TABLE {$this->qn('foo')} ADD {$this->qn('bar')} int(11) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Test' FIRST";

		$actual = MysqlQueryBuilder::addColumn('foo', 'bar', 'int(11)', false, false, '1', 'FIRST', 'Test');

		$this->assertEquals(
			$this->format($expected),
			$this->format($actual)
		);
	}

	/**
	 * Method to test changeColumn().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Mysql\MysqlQueryBuilder::changeColumn
	 */
	public function testChangeColumn()
	{
		$expected = "ALTER TABLE {$this->qn('foo')} CHANGE {$this->qn('bar')} {$this->qn('yoo')} int(11) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Test' FIRST";

		$actual = MysqlQueryBuilder::changeColumn('foo', 'bar', 'yoo', 'int(11)', false, false, '1', 'FIRST', 'Test');

		$this->assertEquals(
			$this->format($expected),
			$this->format($actual)
		);
	}

	/**
	 * Method to test modifyColumn().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Mysql\MysqlQueryBuilder::modifyColumn
	 */
	public function testModifyColumn()
	{
		$expected = "ALTER TABLE {$this->qn('foo')} MODIFY {$this->qn('bar')} int(11) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Test' FIRST";

		$actual = MysqlQueryBuilder::modifyColumn('foo', 'bar', 'int(11)', false, false, '1', 'FIRST', 'Test');

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
	 * @covers Windwalker\Query\Mysql\MysqlQueryBuilder::dropColumn
	 */
	public function testDropColumn()
	{
		$expected = "ALTER TABLE {$this->qn('foo')} DROP {$this->qn('bar')}";

		$actual = MysqlQueryBuilder::dropColumn('foo', 'bar');

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
	 * @covers Windwalker\Query\Mysql\MysqlQueryBuilder::addIndex
	 */
	public function testAddIndex()
	{
		$expected = "ALTER TABLE {$this->qn('foo')} ADD KEY {$this->qn('idx_alias')} ({$this->qn('alias')}, {$this->qn('name')}) COMMENT 'Test Index'";

		$actual = MysqlQueryBuilder::addIndex('foo', 'KEY', 'idx_alias', array('alias', 'name'), 'Test Index');

		$this->assertEquals(
			$this->format($expected),
			$this->format($actual)
		);

		$expected = "ALTER TABLE {$this->qn('foo')} ADD KEY {$this->qn('idx_alias')} ({$this->qn('alias')}) COMMENT 'Test Index'";

		$actual = MysqlQueryBuilder::addIndex('foo', 'KEY', 'idx_alias', 'alias', 'Test Index');

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
	 * @covers Windwalker\Query\Mysql\MysqlQueryBuilder::buildIndexDeclare
	 */
	public function testBuildIndexDeclare()
	{
		$expected = "{$this->qn('idx_alias')} ({$this->qn('alias')})";

		$actual = MysqlQueryBuilder::buildIndexDeclare('idx_alias', 'alias');

		$this->assertEquals(
			$this->format($expected),
			$this->format($actual)
		);

		$expected = "{$this->qn('idx_alias')} ({$this->qn('alias')}, {$this->qn('name')})";

		$actual = MysqlQueryBuilder::buildIndexDeclare('idx_alias', array('alias', 'name'));

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
	 * @covers Windwalker\Query\Mysql\MysqlQueryBuilder::dropIndex
	 */
	public function testDropIndex()
	{
		$expected = "ALTER TABLE {$this->qn('foo')} DROP INDEX {$this->qn('bar')}";

		$actual = MysqlQueryBuilder::dropIndex('foo', 'INDEX', 'bar');

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
	 * @covers Windwalker\Query\Mysql\MysqlQueryBuilder::build
	 */
	public function testBuild()
	{
		$expected = "FLOWER SAKURA SUNFLOWER OLIVE";

		$actual = MysqlQueryBuilder::build('FLOWER', 'SAKURA', 'SUNFLOWER', 'OLIVE');

		$this->assertEquals(
			$this->format($expected),
			$this->format($actual)
		);
	}

	/**
	 * Method to test replace().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Mysql\MysqlQueryBuilder::replace
	 */
	public function testReplace()
	{
		$expected = "REPLACE INTO {$this->qn('foo')} (a,b) VALUES (c, d, e), (f, g, h)";

		$actual = MysqlQueryBuilder::replace('foo', array('a', 'b'), array('c, d, e', 'f, g, h'));

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
	 * @covers Windwalker\Query\Mysql\MysqlQueryBuilder::getQuery
	 */
	public function testGetQuery()
	{
		$this->assertInstanceOf('Windwalker\\Query\\Mysql\\MysqlQuery', MysqlQueryBuilder::getQuery());

		$this->assertSame(MysqlQueryBuilder::getQuery(), MysqlQueryBuilder::getQuery());

		$this->assertNotSame(MysqlQueryBuilder::getQuery(), MysqlQueryBuilder::getQuery(true));
	}
}
