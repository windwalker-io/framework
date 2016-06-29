<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\DataMapper;

use Windwalker\Database\DatabaseFactory;
use Windwalker\Database\Driver\AbstractDatabaseDriver;

/**
 * The DbContainer class.
 *
 * @since  {DEPLOY_VERSION}
 */
class DatabaseContainer
{
	/**
	 * Property db.
	 *
	 * @var  AbstractDatabaseDriver
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
		if (!static::$db || $forceNew)
		{
			static::$db = DatabaseFactory::getDbo($driver, $option, $forceNew);
		}

		return static::$db;
	}

	/**
	 * Method to set property db
	 *
	 * @param   AbstractDatabaseDriver $db
	 *
	 * @return  static  Return self to support chaining.
	 */
	public static function setDb(AbstractDatabaseDriver $db)
	{
		static::$db = $db;
	}
}
