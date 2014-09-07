<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Database\Test\Mysql;

use Windwalker\Database\Driver\Mysql\MysqlDriver;
use Windwalker\Database\Test\AbstractDatabaseCase;
use Windwalker\Query\Mysql\MysqlQuery;

/**
 * Test class of MysqlDriver
 *
 * @since {DEPLOY_VERSION}
 */
class MysqlDriverTest extends AbstractDatabaseCase
{
	/**
	 * Property driver.
	 *
	 * @var  string
	 */
	protected static $driver = 'mysql';

	/**
	 * Property db.
	 *
	 * @var MysqlDriver
	 */
	protected $db;

	/**
	 * Property connection.
	 *
	 * @var \PDO
	 */
	protected $connection;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->db = static::$dbo;
		$this->connection = $this->db->getConnection();
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 *
	 * @return void
	 */
	protected function tearDown()
	{
	}

	/**
	 * Method to test getOption().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Pdo\PdoDriver::getOption
	 */
	public function testGetOption()
	{
		$this->assertEquals(\PDO::ERRMODE_EXCEPTION, $this->db->getOption(\PDO::ATTR_ERRMODE));
	}

	/**
	 * Method to test setOption().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Pdo\PdoDriver::setOption
	 */
	public function testSetOption()
	{
		$this->db->setOption(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);

		$this->assertEquals(\PDO::ERRMODE_SILENT, $this->db->getConnection()->getAttribute(\PDO::ATTR_ERRMODE));
	}

	/**
	 * Method to test getVersion().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Pdo\PdoDriver::getVersion
	 */
	public function testGetVersion()
	{
		$this->assertEquals(
			$this->connection->getAttribute(\PDO::ATTR_SERVER_VERSION),
			$this->db->getVersion()
		);
	}

	/**
	 * Method to test select().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Pdo\PdoDriver::select
	 */
	public function testSelect()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setQuery().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Pdo\PdoDriver::setQuery
	 */
	public function testGetAndSetQuery()
	{
		$q = new MysqlQuery($this->connection);

		$q->select('*')
			->from('wind');

		$this->db->setQuery($q);

		$q2 = $this->db->getQuery();

		$this->assertSame($q, $q2);
	}

	/**
	 * Method to test doExecute().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Pdo\PdoDriver::doExecute
	 */
	public function testDoExecute()
	{
		$this->db->setQuery('INSERT INTO `ww_flower` (`catid`) VALUES ("3")');

		$this->db->execute();

		$this->assertEquals(86, $this->db->getWriter()->insertId());
	}

	/**
	 * Method to test freeResult().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Pdo\PdoDriver::freeResult
	 */
	public function testFreeResult()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getTable().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Pdo\PdoDriver::getTable
	 * @TODO   Implement testGetTable().
	 */
	public function testGetTable()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getDatabase().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Pdo\PdoDriver::getDatabase
	 * @TODO   Implement testGetDatabase().
	 */
	public function testGetDatabase()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getReader().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Pdo\PdoDriver::getReader
	 * @TODO   Implement testGetReader().
	 */
	public function testGetReader()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getWriter().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Pdo\PdoDriver::getWriter
	 * @TODO   Implement testGetWriter().
	 */
	public function testGetWriter()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getTransaction().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Pdo\PdoDriver::getTransaction
	 * @TODO   Implement testGetTransaction().
	 */
	public function testGetTransaction()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test listDatabases().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Pdo\PdoDriver::listDatabases
	 * @TODO   Implement testListDatabases().
	 */
	public function testListDatabases()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getConnection().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::getConnection
	 * @TODO   Implement testGetConnection().
	 */
	public function testGetConnection()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setConnection().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::setConnection
	 * @TODO   Implement testSetConnection().
	 */
	public function testSetConnection()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test execute().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::execute
	 * @TODO   Implement testExecute().
	 */
	public function testExecute()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getCursor().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::getCursor
	 * @TODO   Implement testGetCursor().
	 */
	public function testGetCursor()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getIterator().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::getIterator
	 * @TODO   Implement testGetIterator().
	 */
	public function testGetIterator()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getCurrentDatabase().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::getCurrentDatabase
	 * @TODO   Implement testGetCurrentDatabase().
	 */
	public function testGetCurrentDatabase()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test getPrefix().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::getPrefix
	 * @TODO   Implement testGetPrefix().
	 */
	public function testGetPrefix()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test log().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::log
	 * @TODO   Implement testLog().
	 */
	public function testLog()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setLogger().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::setLogger
	 * @TODO   Implement testSetLogger().
	 */
	public function testSetLogger()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test replacePrefix().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::replacePrefix
	 * @TODO   Implement testReplacePrefix().
	 */
	public function testReplacePrefix()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test splitSql().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::splitSql
	 * @TODO   Implement testSplitSql().
	 */
	public function testSplitSql()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test setDebug().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::setDebug
	 * @TODO   Implement testSetDebug().
	 */
	public function testSetDebug()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test loadAll().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::loadAll
	 * @TODO   Implement testLoadAll().
	 */
	public function testLoadAll()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test loadOne().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::loadOne
	 * @TODO   Implement testLoadOne().
	 */
	public function testLoadOne()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test loadResult().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::loadResult
	 * @TODO   Implement testLoadResult().
	 */
	public function testLoadResult()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test loadColumn().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::loadColumn
	 * @TODO   Implement testLoadColumn().
	 */
	public function testLoadColumn()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test quoteName().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::quoteName
	 * @TODO   Implement testQuoteName().
	 */
	public function testQuoteName()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test qn().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::qn
	 * @TODO   Implement testQn().
	 */
	public function testQn()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test quote().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::quote
	 * @TODO   Implement testQuote().
	 */
	public function testQuote()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test q().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::q
	 * @TODO   Implement testQ().
	 */
	public function testQ()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test escape().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::escape
	 * @TODO   Implement testEscape().
	 */
	public function testEscape()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test e().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::e
	 * @TODO   Implement testE().
	 */
	public function testE()
	{
		// Remove the following lines when you implement this test.
		$this->markTestIncomplete(
			'This test has not been implemented yet.'
		);
	}

	/**
	 * Method to test connect().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Pdo\PdoDriver::connect
	 */
	public function testConnect()
	{
		$this->db->disconnect();

		$this->db->connect();

		$this->assertInstanceOf('PDO', $this->db->getConnection());
	}

	/**
	 * Method to test disconnect().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Pdo\PdoDriver::disconnect
	 */
	public function testDisconnect()
	{
		$this->db->disconnect();

		$this->assertNull($this->db->getConnection());
	}
}
