<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\DataMapper\Adapter;

use Windwalker\Database\DatabaseFactory;

/**
 * Class DatabaseAdapter
 *
 * @since 1.0
 */
abstract class DatabaseAdapter implements DatabaseAdapterInterface
{
	/**
	 * Property instance.
	 *
	 * @var  DatabaseAdapter
	 */
	protected static $instance = null;

	/**
	 * getInstance
	 *
	 * @throws \UnexpectedValueException
	 * @return  DatabaseAdapter
	 */
	public static function getInstance()
	{
		if (!static::$instance)
		{
			static::$instance = new WindwalkerAdapter(DatabaseFactory::getDbo());
		}

		if (is_callable(static::$instance))
		{
			static::$instance = call_user_func(static::$instance);
		}

		if (!(static::$instance instanceof DatabaseAdapter))
		{
			throw new \UnexpectedValueException('DB Adapter instance must be callable or extends DatabaseAdapter.');
		}

		return static::$instance;
	}

	/**
	 * setInstance
	 *
	 * @param object|callable $db
	 *
	 * @return  void
	 */
	public static function setInstance($db)
	{
		static::$instance = $db;
	}
}
