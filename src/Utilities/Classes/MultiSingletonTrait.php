<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Formosa\Utilities\Classes;

/**
 * The MultiSingletonTrait class.
 *
 * @since  {DEPLOY_VERSION}
 */
trait MultiSingletonTrait
{
	/**
	 * Property instances.
	 *
	 * @var  array
	 */
	protected static $instances = array();

	/**
	 * getInstance
	 *
	 * @param string $name
	 *
	 * @return  static
	 */
	public static function getInstance($name)
	{
		if (!empty(static::$instances[$name]))
		{
			return static::$instances[$name];
		}

		return null;
	}

	/**
	 * setInstance
	 *
	 * @param string $name
	 * @param object $instance
	 *
	 * @return  mixed
	 */
	protected static function setInstance($name, $instance)
	{
		return static::$instances[$name] = $instance;
	}

	/**
	 * hasInstance
	 *
	 * @param string $name
	 *
	 * @return  bool
	 */
	protected static function hasInstance($name)
	{
		return isset(static::$instances[$name]);
	}
} 