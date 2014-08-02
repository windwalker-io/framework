<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Database;

use Windwalker\Database\Driver\DatabaseDriver;

/**
 * Class DatabaseFactory
 */
abstract class DatabaseFactory
{
	/**
	 * Property db.
	 *
	 * @var DatabaseDriver
	 */
	protected static $db = null;

	/**
	 * getDbo
	 *
	 * @param array $option
	 * @param bool  $forceNew
	 *
	 * @return  DatabaseDriver
	 */
	public static function getDbo($option = array(), $forceNew = false)
	{
		if (!self::$db || $forceNew)
		{
			self::$db = static::createDbo($option);
		}

		return self::$db;
	}

	/**
	 * setDb
	 *
	 * @param   DatabaseDriver $db
	 *
	 * @return  void
	 */
	public static function setDbo(DatabaseDriver $db)
	{
		self::$db = $db;
	}

	/**
	 * createDbo
	 *
	 * @param array  $options
	 *
	 * @throws  \RuntimeException
	 *
	 * @return  DatabaseDriver
	 */
	public static function createDbo(array $options)
	{
		// Sanitize the database connector options.
		$options['driver']   = preg_replace('/[^A-Z0-9_\.-]/i', '', $options['driver']);
		$options['database'] = (isset($options['database'])) ? $options['database'] : null;
		$options['select']   = (isset($options['select'])) ? $options['select'] : true;

		// Derive the class name from the driver.
		$class = '\\Windwalker\\Database\\Driver\\' . ucfirst(strtolower($options['driver'])) . '\\' . ucfirst(strtolower($options['driver'])) . 'Driver';

		// If the class still doesn't exist we have nothing left to do but throw an exception.  We did our best.
		if (!class_exists($class))
		{
			throw new \RuntimeException(sprintf('Unable to load Database Driver: %s', $options['driver']));
		}

		// Create our new Driver connector based on the options given.
		try
		{
			$instance = new $class($options);
		}
		catch (\RuntimeException $e)
		{
			throw new \RuntimeException(sprintf('Unable to connect to the Database: %s', $e->getMessage()));
		}

		return $instance;
	}
}
