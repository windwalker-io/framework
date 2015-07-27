<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Database\Test\Mysql;

use Windwalker\Database\Driver\Mysql\MysqlDriver;
use Windwalker\Database\Test\AbstractDatabaseCase;

/**
 * The AbstractMysqlTest class.
 * 
 * @since  2.0
 */
abstract class AbstractMysqlTest extends AbstractDatabaseCase
{
	/**
	 * Property driver.
	 *
	 * @var  string
	 */
	protected static $driver = 'mysql';

	/**
	 * Property quote.
	 *
	 * @var  array
	 */
	protected static $quote = array('`', '`');

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
}
