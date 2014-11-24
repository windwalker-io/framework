<?php
/**
 * Part of Windwalker project Test files.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Database\Test;

use Windwalker\Database\DatabaseFactory;
use Windwalker\Database\Driver\Mysql\MysqlDriver;

/**
 * Test class of DatabaseFactory
 *
 * @since {DEPLOY_VERSION}
 */
class DatabaseFactoryTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Method to test getDbo().
	 *
	 * @return void
	 *
	 * @covers Windwalker\Database\DatabaseFactory::getDbo
	 */
	public function testGetDbo()
	{
		$options = $this->getOptions();

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
	 * @covers Windwalker\Database\DatabaseFactory::setDefaultDbo
	 */
	public function testSetDefaultDbo()
	{
		$options = $this->getOptions();

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
	 * @covers Windwalker\Database\DatabaseFactory::createDbo
	 */
	public function testCreateDbo()
	{
		$options = $this->getOptions();

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
		$dsn = DsnResolver::getDsn('mysql');

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
