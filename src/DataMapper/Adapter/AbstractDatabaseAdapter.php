<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\DataMapper\Adapter;

use Windwalker\Database\DatabaseFactory;

/**
 * Class DatabaseAdapter
 *
 * @since 2.0
 */
abstract class AbstractDatabaseAdapter implements DatabaseAdapterInterface
{
	/**
	 * Property instance.
	 *
	 * @var  AbstractDatabaseAdapter
	 */
	protected static $instance = null;

	/**
	 * getInstance
	 *
	 * @throws \UnexpectedValueException
	 * @return  AbstractDatabaseAdapter
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

		if (!(static::$instance instanceof AbstractDatabaseAdapter))
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
