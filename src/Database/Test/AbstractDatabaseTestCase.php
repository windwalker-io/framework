<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Database\Test;

use Windwalker\Database\DatabaseFactory;
use Windwalker\Database\DatabaseHelper;
use Windwalker\Database\Driver\AbstractDatabaseDriver;
use Windwalker\Test\TestHelper;

/**
 * Class DatabaseTestCase
 *
 * @since 2.0
 */
abstract class AbstractDatabaseTestCase extends AbstractQueryTestCase
{
	/**
	 * Property db.
	 *
	 * @var  AbstractDatabaseDriver
	 */
	protected static $dbo = null;

	/**
	 * Property db.
	 *
	 * @var  AbstractDatabaseDriver
	 */
	protected $db = null;

	/**
	 * Property driver.
	 *
	 * @var string
	 */
	protected static $driver = null;

	/**
	 * Property quote.
	 *
	 * @var  array
	 */
	protected static $quote = array('"', '"');

	/**
	 * Property dbname.
	 *
	 * @var string
	 */
	protected static $dbname = '';

	/**
	 * Property dsn.
	 *
	 * @var array
	 */
	protected static $dsn = array();

	/**
	 * Property debug.
	 *
	 * @var  boolean
	 */
	protected static $debug = true;

	/**
	 * setUpBeforeClass
	 *
	 * @throws \LogicException
	 * @return  void
	 */
	public static function setUpBeforeClass()
	{
		if (!static::$driver)
		{
			throw new \LogicException('static::$driver variable is empty.');
		}

		static::$dsn = $dsn = TestDsnResolver::getDsn(static::$driver);

		if (!$dsn)
		{
			static::markTestSkipped('DSN of driver ' . static::$driver . ' not available');
		}

		static::$dbname = $dbname = isset($dsn['dbname']) ? $dsn['dbname'] : null;

		if (!$dbname)
		{
			throw new \LogicException(sprintf('No dbname in %s DSN', static::$driver));
		}

		// Id db exists, return.
		if (static::$dbo)
		{
			static::$dbo->select($dbname);

			return;
		}

		try
		{
			// Use factory create dbo, only create once and will be singleton.
			$db = self::$dbo = DatabaseFactory::getDbo(
				static::$driver,
				array(
					'host'     => isset($dsn['host']) ? $dsn['host'] : null,
					'user'     => isset($dsn['user']) ? $dsn['user'] : null,
					'password' => isset($dsn['pass']) ? $dsn['pass'] : null,
					'port'     => isset($dsn['port']) ? $dsn['port'] : null,
					'prefix'   => isset($dsn['prefix']) ? $dsn['prefix'] : null,
				)
			);
		}
		catch (\RangeException $e)
		{
			static::markTestSkipped($e->getMessage());

			return;
		}

		$database = $db->getDatabase($dbname);

		if (static::$debug)
		{
			$database->drop(true);
		}

		$database->create(true);

		$db->select($dbname);

		// MySQL Strict Mode
		if (static::$driver == 'mysql' && static::$dsn['strict_mode'])
		{
			$db->setQuery("SET sql_mode = 'NO_ENGINE_SUBSTITUTION,STRICT_ALL_TABLES'")->execute();
		}

		static::setupFixtures();
	}

	/**
	 * getInstallSql
	 *
	 * @return  string
	 */
	protected static function getSetupSql()
	{
		return file_get_contents(__DIR__ . '/Stub/' . static::$driver . '.sql');
	}

	/**
	 * setupFixtures
	 *
	 * @return  void
	 */
	protected static function setupFixtures()
	{
		$queries = static::getSetupSql();

		DatabaseHelper::batchQuery(static::$dbo, $queries);
	}

	/**
	 * getTearDownSql
	 *
	 * @return  string
	 */
	protected static function getTearDownSql()
	{
		return 'DROP DATABASE IF EXISTS ' . self::$dbo->quoteName(static::$dbname);
	}

	/**
	 * tearDownFixtures
	 *
	 * @return  void
	 */
	protected function tearDownFixtures()
	{
		$queries = static::getTearDownSql();

		DatabaseHelper::batchQuery(static::$dbo, $queries);
	}

	/**
	 * tearDownAfterClass
	 *
	 * @return  void
	 */
	public static function tearDownAfterClass()
	{
		if (!self::$dbo)
		{
			return;
		}

		static::$debug or static::tearDownFixtures();

		self::$dbo = null;
	}

	/**
	 * Destruct.
	 */
	public function __destruct()
	{
		if (!self::$dbo)
		{
			return;
		}

		static::$debug or static::tearDownFixtures();

		self::$dbo = null;
	}

	/**
	 * Sets up the fixture.
	 *
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	protected function setUp()
	{
		if (empty(static::$dbo))
		{
			$this->markTestSkipped('There is no database driver.');
		}

		parent::setUp();
	}

	/**
	 * Tears down the fixture, for example, closes a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown()
	{
		$this->db->resetMiddlewares();

		$tables = TestHelper::getValue($this->db, 'tables');

		foreach ((array) $tables as $table)
		{
			$table->reset();
		}

		$this->db = null;
	}
}
