<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Database\Test\Mysql;

use Windwalker\Database\Driver\Mysql\MysqlDriver;
use Windwalker\Query\Mysql\MysqlQueryBuilder;

/**
 * Test class of MysqlDatabase
 *
 * @since {DEPLOY_VERSION}
 */
class MysqlDatabaseTest extends AbstractMysqlTest
{
	/**
	 * testAutoSelect
	 *
	 * @return  void
	 */
	public function testAutoSelect()
	{
		$option = static::$dsn;

		$option['database'] = static::$dsn['dbname'];
		$option['password'] = static::$dsn['pass'];

		$db = new MysqlDriver(null, $option);

		$db->setQuery('SELECT * FROM #__flower')->loadAll();
	}

	/**
	 * Method to test select().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Mysql\MysqlDatabase::select
	 */
	public function testSelect()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test create().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Mysql\MysqlDatabase::create
	 */
	public function testCreate()
	{
		$database = $this->db->getDatabase('windwalker_foo_test');

		$database->create(true);

		$dbs = $this->db->getReader('SHOW DATABASES')->loadColumn();

		$this->assertContains('windwalker_foo_test', $dbs, 'DB: "windwalker_foo_test" not in db name list.');
	}

	/**
	 * Method to test drop().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Mysql\MysqlDatabase::drop
	 */
	public function testDrop()
	{
		$database = $this->db->getDatabase('windwalker_foo_test');

		$database->drop(true);

		$dbs = $this->db->getReader('SHOW DATABASES')->loadColumn();

		$this->assertNotContains('windwalker_foo_test', $dbs, 'DB: "windwalker_foo_test" should not in db name list.');
	}

	/**
	 * Method to test rename().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Mysql\MysqlDatabase::rename
	 */
	public function testRename()
	{
		$database = $this->db->getDatabase(static::$dsn['dbname']);

		$tables = $database->getTables(true);

		$database = $database->rename('windwalker_bar_test');

		// Check new db object
		$this->assertEquals('windwalker_bar_test', $database->getName(), 'Returned object should be new database');

		$dbs = $this->db->getReader('SHOW DATABASES')->loadColumn();

		// Check new DB exists
		$this->assertContains('windwalker_bar_test', $dbs, 'DB: "windwalker_bar_test" not in db name list.');

		// Check new DB tables
		$this->assertEquals($tables, $database->getTables(true));

		// Rename back
		$database->rename(static::$dsn['dbname']);
	}

	/**
	 * Method to test getTables().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Mysql\MysqlDatabase::getTables
	 */
	public function testGetTables()
	{
		$tables = $this->db->getDatabase(static::$dbname)->getTables();

		$this->assertEquals(
			array(
				static::$dsn['prefix'] . 'categories',
				static::$dsn['prefix'] . 'flower',
				static::$dsn['prefix'] . 'nestedsets',
			),
			$tables
		);
	}

	/**
	 * Method to test getTableDetails().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Mysql\MysqlDatabase::getTableDetails
	 */
	public function testGetTableDetails()
	{
		$tables = $this->db->getDatabase(static::$dbname)->getTableDetails();

		$this->assertEquals(static::$dsn['prefix'] . 'flower', $tables[static::$dsn['prefix'] . 'flower']->Name);
	}

	/**
	 * Method to test getTableDetail().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Mysql\MysqlDatabase::getTableDetail
	 */
	public function testGetTableDetail()
	{
		$table = $this->db->getDatabase(static::$dbname)->getTableDetail('#__flower');

		$this->assertEquals(static::$dsn['prefix'] . 'flower', $table->Name);
	}

	/**
	 * Destructor
	 */
	public function __destruct()
	{
		$this->db->setQuery(MysqlQueryBuilder::dropDatabase('windwalker_foo_test', true))->execute();
		$this->db->setQuery(MysqlQueryBuilder::dropDatabase('windwalker_bar_test', true))->execute();

		parent::__destruct();
	}

	/**
	 * tearDownAfterClass
	 *
	 * @return  void
	 */
	public static function tearDownAfterClass()
	{
		static::$dbo->setQuery(MysqlQueryBuilder::dropDatabase('windwalker_foo_test', true))->execute();
		static::$dbo->setQuery(MysqlQueryBuilder::dropDatabase('windwalker_bar_test', true))->execute();

		parent::tearDownAfterClass();
	}
}
