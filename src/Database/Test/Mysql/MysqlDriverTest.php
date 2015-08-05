<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Database\Test\Mysql;

use Windwalker\Query\Mysql\MysqlQuery;

/**
 * Test class of MysqlDriver
 *
 * @since 2.0
 */
class MysqlDriverTest extends AbstractMysqlTest
{
	/**
	 * Method to test getOption().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Mysql\MysqlDriver::getOption
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
	 * @covers Windwalker\Database\Driver\Mysql\MysqlDriver::setOption
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
	 * @covers Windwalker\Database\Driver\Mysql\MysqlDriver::getVersion
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
	 * @covers Windwalker\Database\Driver\Mysql\MysqlDriver::select
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
	 * @covers Windwalker\Database\Driver\Mysql\MysqlDriver::setQuery
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
	 * Method to test execute().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Mysql\MysqlDriver::doExecute
	 */
	public function testExecute()
	{
		$this->db->setQuery('INSERT INTO `#__flower` (`catid`) VALUES ("3")');

		$this->db->execute();

		$this->assertEquals(86, $this->db->getWriter()->insertId());
	}

	/**
	 * Method to test freeResult().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Mysql\MysqlDriver::freeResult
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
	 * @covers Windwalker\Database\Driver\Mysql\MysqlDriver::getTable
	 */
	public function testGetTable()
	{
		$table = $this->db->getTable('#__flower');
		$driver = ucfirst(static::$driver);

		$this->assertInstanceOf(
			sprintf('Windwalker\\Database\\Driver\\%s\\%sTable', $driver, $driver),
			$table
		);

		$this->assertEquals('#__flower', $table->getName());
	}

	/**
	 * Method to test getDatabase().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Mysql\MysqlDriver::getDatabase
	 */
	public function testGetDatabase()
	{
		$database = $this->db->getDatabase(static::$dbname);
		$driver = ucfirst(static::$driver);

		$this->assertInstanceOf(
			sprintf('Windwalker\\Database\\Driver\\%s\\%sDatabase', $driver, $driver),
			$database
		);

		$this->assertEquals(static::$dbname, $database->getName());
	}

	/**
	 * Method to test getReader().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Mysql\MysqlDriver::getReader
	 */
	public function testGetReader()
	{
		$reader = $this->db->getReader();
		$driver = ucfirst(static::$driver);

		$this->assertInstanceOf(
			sprintf('Windwalker\\Database\\Driver\\%s\\%sReader', $driver, $driver),
			$reader
		);
	}

	/**
	 * Method to test getWriter().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Mysql\MysqlDriver::getWriter
	 */
	public function testGetWriter()
	{
		$writer = $this->db->getWriter();
		$driver = ucfirst(static::$driver);

		$this->assertInstanceOf(
			sprintf('Windwalker\\Database\\Driver\\%s\\%sWriter', $driver, $driver),
			$writer
		);
	}

	/**
	 * Method to test getTransaction().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Mysql\MysqlDriver::getTransaction
	 */
	public function testGetTransaction()
	{
		// Test no nested
		$trans = $this->db->getTransaction(true);

		$this->assertTrue($trans->getNested());

		// Test get
		$trans = $this->db->getTransaction();
		$driver = ucfirst(static::$driver);

		$this->assertInstanceOf(
			sprintf('Windwalker\\Database\\Driver\\%s\\%sTransaction', $driver, $driver),
			$trans
		);
	}

	/**
	 * Method to test listDatabases().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Mysql\MysqlDriver::listDatabases
	 */
	public function testListDatabases()
	{
		$dbs = $this->db->setQuery('SHOW DATABASES')->loadColumn();

		$dbList = $this->db->listDatabases();

		$this->assertEquals($dbs, $dbList);
	}

	/**
	 * Method to test getConnection().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::getConnection
	 */
	public function testGetConnection()
	{
		$this->assertInstanceOf('PDO', $this->db->getConnection());
	}

	/**
	 * Method to test setConnection().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::setConnection
	 */
	public function testSetConnection()
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
	 */
	public function testGetCursor()
	{
		$this->assertInstanceOf('PDOStatement', $this->db->getCursor());
	}

	/**
	 * Method to test getIterator().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::getIterator
	 */
	public function testGetIterator()
	{
		$this->assertInstanceOf(
			sprintf('Windwalker\\Database\\Iterator\\DataIterator'),
			$this->db->setQuery('SELECT * FROM #__flower')->getIterator()
		);
	}

	/**
	 * Method to test getCurrentDatabase().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::getCurrentDatabase
	 */
	public function testGetCurrentDatabase()
	{
		$this->assertEquals(static::$dbname, $this->db->getCurrentDatabase());
	}

	/**
	 * Method to test getPrefix().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::getPrefix
	 */
	public function testGetPrefix()
	{
		$this->assertEquals(static::$dsn['prefix'], $this->db->getPrefix());
	}

	/**
	 * Method to test log().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::log
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
	 */
	public function testReplacePrefix()
	{
		$this->assertEquals(
			'SELECT * FROM ' . static::$dsn['prefix'] . 'flower WHERE id = 1',
			$this->db->replacePrefix('SELECT * FROM #__flower WHERE id = 1')
		);
	}

	/**
	 * Method to test splitSql().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::splitSql
	 */
	public function testSplitSql()
	{
		$sql = <<<SQL
SELECT * FROM ww_flower WHERE id = 1;
SELECT * FROM ww_flower WHERE id = 2;
SELECT * FROM ww_flower WHERE id = 3;
SQL;

		$sqls = $this->db->splitSql($sql);

		$this->assertEquals(
			'SELECT * FROM ww_flower WHERE id = 3;',
			trim($sqls[2])
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
	 */
	public function testLoadAll()
	{
		$query = $this->db->getQuery(true);

		$query->select('*')
			->from('#__flower')
			->limit(3);

		$items = $this->db->setQuery($query)->loadAll();

		$this->assertEquals(1, $items[0]->id);
		$this->assertEquals('Amaryllis', $items[1]->title);
		$this->assertEquals(3, $items[2]->ordering);

		$items = $this->db->setQuery($query)->loadAll('title', 'assoc');

		$this->assertEquals('Amaryllis', $items['Amaryllis']['title']);
	}

	/**
	 * Method to test loadOne().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::loadOne
	 */
	public function testLoadOne()
	{
		$query = $this->db->getQuery(true);

		$query->select('*')
			->from('#__flower')
			->where('id = 4');

		$item = $this->db->setQuery($query)->loadOne();

		$this->assertEquals('Apple Blossom', $item->title);

		$item = $this->db->setQuery($query)->loadOne('assoc');

		$this->assertEquals('Apple Blossom', $item['title']);

		// Test bind params
		$query = $this->db->getQuery(true);

		$id = 4;

		echo $query->select('*')
			->from('#__flower')
			->where('id = :id')
			->bind('id', $id);

		$item = $this->db->setQuery($query)->loadOne();

		$this->assertEquals('Apple Blossom', $item->title);

		$item = $this->db->setQuery($query)->loadOne('assoc');

		$this->assertEquals('Apple Blossom', $item['title']);
	}

	/**
	 * Method to test loadResult().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::loadResult
	 */
	public function testLoadResult()
	{
		$query = $this->db->getQuery(true);

		$query->select('title')
			->from('#__flower')
			->where('id = 5');

		$item = $this->db->setQuery($query)->loadResult();

		$this->assertEquals('Aster', $item);
	}

	/**
	 * Method to test loadColumn().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::loadColumn
	 */
	public function testLoadColumn()
	{
		$query = $this->db->getQuery(true);

		$query->select('title')
			->from('#__flower')
			->where('ordering < 10');

		$items = $this->db->setQuery($query)->loadColumn();

		$this->assertEquals('Bachelor Button', $items[7]);
	}

	/**
	 * Method to test quoteName().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::quoteName
	 */
	public function testQuoteName()
	{
		$name = '#__flower';

		$this->assertEquals(
			static::$quote[0] . $name . static::$quote[1],
			$this->db->quoteName($name)
		);
	}

	/**
	 * Method to test qn().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::qn
	 */
	public function testQn()
	{
		$name = '#__flower';

		$this->assertEquals(
			static::$quote[0] . $name . static::$quote[1],
			$this->db->qn($name)
		);
	}

	/**
	 * Method to test quote().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::quote
	 */
	public function testQuote()
	{
		$text = "Simon can't fly.\nSakura is flower.";

		$this->assertEquals(
			"'" . $this->db->escape($text) . "'",
			$this->db->quote($text)
		);
	}

	/**
	 * Method to test q().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::q
	 */
	public function testQ()
	{
		$text = "Simon can't fly.\nSakura is flower.";

		$this->assertEquals(
			"'" . $this->db->escape($text) . "'",
			$this->db->q($text)
		);
	}

	/**
	 * Method to test escape().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::escape
	 */
	public function testEscape()
	{
		$string = "foo \"'_-!@#$%^&*() \n \t \r \0";

		$this->assertEquals(
			$this->db->getQuery(true)->escape($string, true),
			$this->db->escape($string)
		);
	}

	/**
	 * Method to test e().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\DatabaseDriver::e
	 */
	public function testE()
	{
		$string = "foo \"'_-!@#$%^&*() \n \t \r \0";

		$this->assertEquals(
			$this->db->getQuery(true)->escape($string, true),
			$this->db->e($string)
		);
	}

	/**
	 * Method to test disconnect().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Mysql\MysqlDriver::setProfilerHandler
	 */
	public function testSetProfilerHandler()
	{
		$profiler = array();

		$this->db->setProfilerHandler(
			function($db, $sql) use (&$profiler)
			{
				$profiler['db'] = $db;
				$profiler['sql'] = $sql;
				$profiler['before'] = true;
			},
			function($db, $sql) use (&$profiler)
			{
				$profiler['db'] = $db;
				$profiler['sql'] = $sql;
				$profiler['after'] = true;
			}
		);

		$this->db->setQuery('SELECT * FROM #__flower')->execute();

		$this->assertSame($this->db, $profiler['db']);
		$this->assertSame('SELECT * FROM ' . static::$dsn['prefix'] . 'flower', $profiler['sql']);

		$this->assertTrue($profiler['before']);
		$this->assertTrue($profiler['after']);
	}

	/**
	 * Method to test connect().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\Driver\Mysql\MysqlDriver::connect
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
	 * @covers Windwalker\Database\Driver\Mysql\MysqlDriver::disconnect
	 */
	public function testDisconnect()
	{
		$this->db->disconnect();

		$this->assertNull($this->db->getConnection());
	}
}
