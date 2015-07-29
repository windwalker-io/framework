<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Query;

/**
 * The Connection Container class.
 * 
 * @since  2.0
 */
abstract class ConnectionContainer
{
	/**
	 * Property connections.
	 *
	 * @var  \PDO[]|resource[]
	 */
	protected static $connections = array();

	/**
	 * getConnection
	 *
	 * @param string $driver
	 *
	 * @return  null|\PDO|resource
	 */
	public static function getConnection($driver)
	{
		$driver = strtolower($driver);

		if (empty(static::$connections[$driver]))
		{
			return null;
		}

		return static::$connections[$driver];
	}

	/**
	 * setConnection
	 *
	 * @param string        $driver
	 * @param \PDO|resource $connection
	 *
	 * @return  void
	 */
	public static function setConnection($driver, $connection)
	{
		$driver = strtolower($driver);

		static::$connections[$driver] = $connection;
	}
}
