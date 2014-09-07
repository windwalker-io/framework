<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Database;

use Windwalker\Database\Driver\DatabaseDriver;

/**
 * Class DatabaseFactory
 */
abstract class DatabaseFactory
{
	/**
	 * The default DB object.
	 *
	 * @var DatabaseDriver
	 */
	protected static $db = null;

	/**
	 * Property instances.
	 *
	 * @var  array
	 */
	protected static $instances = array();

	/**
	 * getDbo
	 *
	 * @param string $driver
	 * @param array  $option
	 * @param bool   $forceNew
	 *
	 * @throws \InvalidArgumentException
	 * @return  DatabaseDriver
	 */
	public static function getDbo($driver, $option = array(), $forceNew = false)
	{
		$option['driver'] = $driver;

		// Create new instance if this driver not exists.
		if (empty(self::$instances[$driver]) || $forceNew)
		{
			self::$instances[$driver] = static::createDbo($option);
		}

		// Set default DB object.
		if (!self::$db)
		{
			self::$db = self::$instances[$driver];
		}

		return self::$instances[$driver];
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
			$instance = new $class(null, $options);
		}
		catch (\RuntimeException $e)
		{
			throw new \RuntimeException(sprintf('Unable to connect to the Database: %s', $e->getMessage()));
		}

		return $instance;
	}
}
