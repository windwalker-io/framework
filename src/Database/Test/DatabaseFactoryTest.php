<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Database\Test;

use Windwalker\Database\DatabaseFactory;
use Windwalker\Database\Driver\Mysql\MysqlDriver;

/**
 * Test class of DatabaseFactory
 *
 * @since 2.0
 */
class DatabaseFactoryTest extends \PHPUnit\Framework\TestCase
{
	/**
	 * Method to test getDbo().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Database\DatabaseFactory::getDbo
	 */
	public function testGetDbo()
	{
		$options = $this->getOptions();

		if ($options === false)
		{
			$this->markTestSkipped('No mysql test DSN');
		}

		$this->assertInstanceOf('Windwalker\\Database\\Driver\\Mysql\\MysqlDriver', DatabaseFactory::getDbo('mysql', $options));

		$this->resetDatabaseFactory();

		// Using custom resource
		$options['resource'] = new \PDO('mysql:host=' . $options['host'] . ';', $options['user'], $options['password']);

		$db = DatabaseFactory::getDbo('mysql', $options);

		$this->assertSame($options['resource'], $db->getConnection());
	}

	/**
	 * Method to test setDefaultDbo().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Database\DatabaseFactory::setDefaultDbo
	 */
	public function testSetDefaultDbo()
	{
		$options = $this->getOptions();

		if ($options === false)
		{
			$this->markTestSkipped('No mysql test DSN');
		}

		$db = new MysqlDriver(null, $options);

		DatabaseFactory::setDefaultDbo($db);

		$this->assertSame($db, DatabaseFactory::getDbo());

		$this->resetDatabaseFactory();
	}

	/**
	 * Method to test createDbo().
	 *
	 * @return void
	 *
	 * @covers \Windwalker\Database\DatabaseFactory::createDbo
	 */
	public function testCreateDbo()
	{
		$options = $this->getOptions();

		if ($options === false)
		{
			$this->markTestSkipped('No mysql test DSN');
		}

		$this->assertInstanceOf('Windwalker\\Database\\Driver\\Mysql\\MysqlDriver', DatabaseFactory::createDbo('mysql', $options));

		$this->resetDatabaseFactory();
	}

	/**
	 * getOptions
	 *
	 * @return  array
	 */
	public static function getOptions()
	{
		// Only use mysql to test
		$dsn = TestDsnResolver::getDsn('mysql');

		if ($dsn === false)
		{
			return false;
		}

		$options = array(
			'host' => $dsn['host'],
			'user' => $dsn['user'],
			'password' => $dsn['pass']
		);

		return $options;
	}

	/**
	 * resetDatabaseFactory
	 *
	 * @return  void
	 */
	public static function resetDatabaseFactory()
	{
		DatabaseFactory::setDbo('mysql', null);
		DatabaseFactory::setDefaultDbo(null);
	}
}
