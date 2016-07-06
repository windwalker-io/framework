<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\DataMapper;

use Windwalker\Database\DatabaseFactory;
use Windwalker\Database\Driver\AbstractDatabaseDriver;

/**
 * The DbContainer class.
 *
 * @since  3.0-beta2
 */
class DatabaseContainer
{
	/**
	 * Property db.
	 *
	 * @var  AbstractDatabaseDriver|callable
	 */
	protected static $db;

	/**
	 * Method to get property Db
	 *
	 * @param string   $driver
	 * @param array    $option
	 * @param boolean  $forceNew
	 *
	 * @return AbstractDatabaseDriver
	 */
	public static function getDb($driver = null, $option = array(), $forceNew = false)
	{
		if (is_callable(static::$db))
		{
			static::$db = call_user_func(static::$db);
		}

		if (!static::$db || $forceNew)
		{
			static::$db = DatabaseFactory::getDbo($driver, $option, $forceNew);
		}

		return static::$db;
	}

	/**
	 * Method to set property db
	 *
	 * @param   AbstractDatabaseDriver|callable $db
	 *
	 * @return  static  Return self to support chaining.
	 */
	public static function setDb($db)
	{
		if (!is_callable($db) && !$db instanceof AbstractDatabaseDriver)
		{
			throw new \InvalidArgumentException('Please use AbstractDatabaseDriver or callable as global database driver.');
		}

		static::$db = $db;
	}
}
