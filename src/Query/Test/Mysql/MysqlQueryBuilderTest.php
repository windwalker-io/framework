<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Query\Test\Mysql;

use Windwalker\Query\Mysql\MysqlQueryBuilder;

/**
 * Test class of MysqlQueryBuilder
 *
 * @since 2.0
 */
class MysqlQueryBuilderTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Property qn.
	 *
	 * @var  string
	 */
	protected $qn = '`';

	/**
	 * Method to test showDatabases().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Query\Mysql\MysqlQueryBuilder::showDatabases
	 */
	public function testShowDatabases()
	{
		$expected = "SHOW DATABASES WHERE a = b";

		$actual = MysqlQueryBuilder::showDatabases('a = b');

		$this->assertEquals(\SqlFormatter::compress($expected), \SqlFormatter::compress($actual));
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
		$expected = "CREATE DATABASE {$this->qn}foo{$this->qn}";

		$actual = MysqlQueryBuilder::createDatabase('foo');

		$this->assertEquals(
			\SqlFormatter::compress($expected),
			\SqlFormatter::compress($actual)
		);

		$expected = "CREATE DATABASE IF NOT EXISTS {$this->qn}foo{$this->qn}";

		$actual = MysqlQueryBuilder::createDatabase('foo', true);

		$this->assertEquals(
			\SqlFormatter::compress($expected),
			\SqlFormatter::compress($actual)
		);

		$expected = "CREATE DATABASE IF NOT EXISTS {$this->qn}foo{$this->qn} CHARACTER SET='utf8' COLLATE='bar'";

		$actual = MysqlQueryBuilder::createDatabase('foo', true, 'utf8', 'bar');

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
	 * @covers Windwalker\Query\Mysql\MysqlQueryBuilder::dropDatabase
	 */
	public function testDropDatabase()
	{
		$expected = "DROP DATABASE {$this->qn}foo{$this->qn}";

		$actual = MysqlQueryBuilder::dropDatabase('foo');

		$this->assertEquals(
			\SqlFormatter::compress($expected),
			\SqlFormatter::compress($actual)
		);

		$expected = "DROP DATABASE IF EXISTS {$this->qn}foo{$this->qn}";

		$actual = MysqlQueryBuilder::dropDatabase('foo', true);

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
	 * @covers Windwalker\Query\Mysql\MysqlQueryBuilder::showTableColumns
	 */
	public function testShowTableColumns()
	{
		$expected = "SHOW COLUMNS FROM {$this->qn}foo{$this->qn}";

		$actual = MysqlQueryBuilder::showTableColumns('foo');

		$this->assertEquals(
			\SqlFormatter::compress($expected),
			\SqlFormatter::compress($actual)
		);

		$expected = "SHOW FULL COLUMNS FROM {$this->qn}foo{$this->qn} WHERE a = b";

		$actual = MysqlQueryBuilder::showTableColumns('foo', true, 'a = b');

		$this->assertEquals(
			\SqlFormatter::compress($expected),
			\SqlFormatter::compress($actual)
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
		$expected = "SHOW TABLE STATUS FROM {$this->qn}foo{$this->qn}";

		$actual = MysqlQueryBuilder::showDbTables('foo');

		$this->assertEquals(
			\SqlFormatter::compress($expected),
			\SqlFormatter::compress($actual)
		);

		$expected = "SHOW TABLE STATUS FROM {$this->qn}foo{$this->qn} WHERE a = b";

		$actual = MysqlQueryBuilder::showDbTables('foo', 'a = b');

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
	 * @covers Windwalker\Query\Mysql\MysqlQueryBuilder::createTable
	 */
	public function testCreateTable()
	{
		$expected = <<<SQL
CREATE TABLE IF NOT EXISTS {$this->qn}foo{$this->qn} (
  {$this->qn}id{$this->qn} int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  {$this->qn}name{$this->qn} varchar(255) NOT NULL COMMENT 'Member Name',
  {$this->qn}email{$this->qn} varchar(255) NOT NULL COMMENT 'Member email',
  PRIMARY KEY ({$this->qn}id{$this->qn}),
  KEY {$this->qn}idx_alias{$this->qn} ({$this->qn}email{$this->qn})
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
			\SqlFormatter::compress($expected),
			\SqlFormatter::compress($actual)
		);

		$expected = <<<SQL
CREATE TABLE {$this->qn}foo{$this->qn} (
  {$this->qn}id{$this->qn} int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
  {$this->qn}name{$this->qn} varchar(255) NOT NULL COMMENT 'Member Name',
  {$this->qn}email{$this->qn} varchar(255) NOT NULL COMMENT 'Member email',
  PRIMARY KEY ({$this->qn}id{$this->qn}, {$this->qn}email{$this->qn}),
  UNIQUE KEY {$this->qn}idx_alias{$this->qn} ({$this->qn}email{$this->qn}, {$this->qn}id{$this->qn})
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
			\SqlFormatter::compress($expected),
			\SqlFormatter::compress($actual)
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
		$expected = "DROP TABLE {$this->qn}foo{$this->qn}";

		$actual = MysqlQueryBuilder::dropTable('foo');

		$this->assertEquals(
			\SqlFormatter::compress($expected),
			\SqlFormatter::compress($actual)
		);

		$expected = "DROP TABLE IF EXISTS {$this->qn}foo{$this->qn}";

		$actual = MysqlQueryBuilder::dropTable('foo', true);

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
	 * @covers Windwalker\Query\Mysql\MysqlQueryBuilder::alterColumn
	 */
	public function testAlterColumn()
	{
		$expected = "ALTER TABLE {$this->qn}foo{$this->qn} MODIFY {$this->qn}bar{$this->qn} int(11) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Test' FIRST";

		$actual = MysqlQueryBuilder::alterColumn('MODIFY', 'foo', 'bar', 'int(11)', false, false, '1', 'FIRST', 'Test');

		$this->assertEquals(
			\SqlFormatter::compress($expected),
			\SqlFormatter::compress($actual)
		);

		$expected = "ALTER TABLE {$this->qn}foo{$this->qn} CHANGE {$this->qn}bar{$this->qn} {$this->qn}yoo{$this->qn} text AFTER {$this->qn}id{$this->qn}";

		$actual = MysqlQueryBuilder::alterColumn('CHANGE', 'foo', array('bar', 'yoo'), 'text', true, true, null, 'AFTER id', null);

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
	 * @covers Windwalker\Query\Mysql\MysqlQueryBuilder::addColumn
	 */
	public function testAddColumn()
	{
		$expected = "ALTER TABLE {$this->qn}foo{$this->qn} ADD {$this->qn}bar{$this->qn} int(11) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Test' FIRST";

		$actual = MysqlQueryBuilder::addColumn('foo', 'bar', 'int(11)', false, false, '1', 'FIRST', 'Test');

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
	 * @covers Windwalker\Query\Mysql\MysqlQueryBuilder::changeColumn
	 */
	public function testChangeColumn()
	{
		$expected = "ALTER TABLE {$this->qn}foo{$this->qn} CHANGE {$this->qn}bar{$this->qn} {$this->qn}yoo{$this->qn} int(11) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Test' FIRST";

		$actual = MysqlQueryBuilder::changeColumn('foo', 'bar', 'yoo', 'int(11)', false, false, '1', 'FIRST', 'Test');

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
	 * @covers Windwalker\Query\Mysql\MysqlQueryBuilder::modifyColumn
	 */
	public function testModifyColumn()
	{
		$expected = "ALTER TABLE {$this->qn}foo{$this->qn} MODIFY {$this->qn}bar{$this->qn} int(11) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Test' FIRST";

		$actual = MysqlQueryBuilder::modifyColumn('foo', 'bar', 'int(11)', false, false, '1', 'FIRST', 'Test');

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
	 * @covers Windwalker\Query\Mysql\MysqlQueryBuilder::dropColumn
	 */
	public function testDropColumn()
	{
		$expected = "ALTER TABLE {$this->qn}foo{$this->qn} DROP {$this->qn}bar{$this->qn}";

		$actual = MysqlQueryBuilder::dropColumn('foo', 'bar');

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
	 * @covers Windwalker\Query\Mysql\MysqlQueryBuilder::addIndex
	 */
	public function testAddIndex()
	{
		$expected = "ALTER TABLE {$this->qn}foo{$this->qn} ADD KEY {$this->qn}idx_alias{$this->qn} ({$this->qn}alias{$this->qn}, {$this->qn}name{$this->qn}) COMMENT 'Test Index'";

		$actual = MysqlQueryBuilder::addIndex('foo', 'KEY', 'idx_alias', array('alias', 'name'), 'Test Index');

		$this->assertEquals(
			\SqlFormatter::compress($expected),
			\SqlFormatter::compress($actual)
		);

		$expected = "ALTER TABLE {$this->qn}foo{$this->qn} ADD KEY {$this->qn}idx_alias{$this->qn} ({$this->qn}alias{$this->qn}) COMMENT 'Test Index'";

		$actual = MysqlQueryBuilder::addIndex('foo', 'KEY', 'idx_alias', 'alias', 'Test Index');

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
	 * @covers Windwalker\Query\Mysql\MysqlQueryBuilder::buildIndexDeclare
	 */
	public function testBuildIndexDeclare()
	{
		$expected = "{$this->qn}idx_alias{$this->qn} ({$this->qn}alias{$this->qn})";

		$actual = MysqlQueryBuilder::buildIndexDeclare('idx_alias', 'alias');

		$this->assertEquals(
			\SqlFormatter::compress($expected),
			\SqlFormatter::compress($actual)
		);

		$expected = "{$this->qn}idx_alias{$this->qn} ({$this->qn}alias{$this->qn}, {$this->qn}name{$this->qn})";

		$actual = MysqlQueryBuilder::buildIndexDeclare('idx_alias', array('alias', 'name'));

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
	 * @covers Windwalker\Query\Mysql\MysqlQueryBuilder::dropIndex
	 */
	public function testDropIndex()
	{
		$expected = "ALTER TABLE {$this->qn}foo{$this->qn} DROP INDEX {$this->qn}bar{$this->qn}";

		$actual = MysqlQueryBuilder::dropIndex('foo', 'INDEX', 'bar');

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
	 * @covers Windwalker\Query\Mysql\MysqlQueryBuilder::build
	 */
	public function testBuild()
	{
		$expected = "FLOWER SAKURA SUNFLOWER OLIVE";

		$actual = MysqlQueryBuilder::build('FLOWER', 'SAKURA', 'SUNFLOWER', 'OLIVE');

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
	 * @covers Windwalker\Query\Mysql\MysqlQueryBuilder::replace
	 */
	public function testReplace()
	{
		$expected = "REPLACE INTO {$this->qn}foo{$this->qn} (a,b) VALUES (c, d, e), (f, g, h)";

		$actual = MysqlQueryBuilder::replace('foo', array('a', 'b'), array('c, d, e', 'f, g, h'));

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
	 * @covers Windwalker\Query\Mysql\MysqlQueryBuilder::getQuery
	 */
	public function testGetQuery()
	{
		$this->assertInstanceOf('Windwalker\\Query\\Mysql\\MysqlQuery', MysqlQueryBuilder::getQuery());

		$this->assertSame(MysqlQueryBuilder::getQuery(), MysqlQueryBuilder::getQuery());

		$this->assertNotSame(MysqlQueryBuilder::getQuery(), MysqlQueryBuilder::getQuery(true));
	}
}
