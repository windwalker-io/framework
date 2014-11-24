<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Environment;

/**
 * The ServerHelper class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class ServerHelper
{
	/**
	 * Property server.
	 *
	 * @var Server
	 */
	protected static $server;

	/**
	 * isWindows
	 *
	 * @return  boolean
	 */
	public static function isWindows()
	{
		return static::getServer()->isWin();
	}

	/**
	 * isLinux
	 *
	 * @return  boolean
	 */
	public static function isLinux()
	{
		return static::getServer()->isLinux();
	}

	/**
	 * isUnix
	 *
	 * @return  boolean
	 */
	public static function isUnix()
	{
		return static::getServer()->isUnix();
	}

	/**
	 * getServer
	 *
	 * @return  Server
	 */
	public static function getServer()
	{
		if (!static::$server)
		{
			static::$server = new Server;
		}

		return static::$server;
	}

	/**
	 * Method to set property server
	 *
	 * @param   Server $server
	 *
	 * @return  void
	 */
	public static function setServer($server)
	{
		static::$server = $server;
	}
}
