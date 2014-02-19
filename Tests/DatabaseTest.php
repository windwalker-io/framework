<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\DataMapper\Tests;

use Joomla\Database\DatabaseDriver;
use Windwalker\DataMapper\Database\DatabaseFactory;

/**
 * Class DatabaseTestCase
 *
 * @since 1.0
 */
abstract class DatabaseTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * Property db.
	 *
	 * @var  DatabaseDriver
	 */
	protected static $dbo = null;

	/**
	 * Property db.
	 *
	 * @var  DatabaseDriver
	 */
	protected $db = null;

	/**
	 * setUpBeforeClass
	 *
	 * @return  void
	 */
	public static function setUpBeforeClass()
	{
		// First let's look to see if we have a DSN defined or in the environment variables.
		if (defined('DB_HOST') || getenv('DB_HOST'))
		{
			$dsn = defined('DB_HOST') ? DB_HOST : getenv('DB_HOST');
		}
		else
		{
			return;
		}

		$db = self::$dbo = DatabaseFactory::getDbo();

		$db->setQuery('CREATE DATABASE IF NOT EXISTS ' . DB_DBNAME)->execute();

		$db->select(DB_DBNAME);

		$queries = file_get_contents(__DIR__ . '/Stubs/data.sql');

		$queries = $db->splitSql($queries);

		foreach ($queries as $query)
		{
			$query = trim($query);

			if ($query)
			{
				$db->setQuery($query)->execute();
			}
		}
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

		self::$dbo->setQuery('DROP DATABASE IF EXISTS ' . DB_DBNAME)->execute();

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

		self::$dbo->setQuery('DROP DATABASE IF EXISTS ' . DB_DBNAME)->execute();

		self::$dbo = null;
	}

	/**
	 * Sets up the fixture.
	 *
	 * This method is called before a test is executed.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function setUp()
	{
		if (empty(static::$dbo))
		{
			$this->markTestSkipped('There is no database driver.');
		}

		parent::setUp();
	}
}
