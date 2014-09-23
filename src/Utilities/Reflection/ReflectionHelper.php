<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Utilities\Reflection;

use Windwalker\String\StringNormalise;

/**
 * Reflection Helper
 *
 * @since {DEPLOY_VERSION}
 */
class ReflectionHelper
{
	/**
	 * The reflections cache.
	 *
	 * @var  array
	 */
	protected static $refs = array();

	/**
	 * Get a new ReflectionClass.
	 *
	 * @param string|object $class The class name.
	 *
	 * @return  \ReflectionClass Reflection instance.
	 */
	public static function get($class)
	{
		return static::getReflection($class);
	}

	/**
	 * getReflection
	 *
	 * @param string|object $class The class or object to get reflection.
	 *
	 * @return  \ReflectionClass Reflection instance.
	 *
	 * @throws \InvalidArgumentException
	 */
	protected static function getReflection($class)
	{
		if (is_object($class))
		{
			$class = get_class($class);
		}

		if (!is_string($class))
		{
			throw new \InvalidArgumentException('ReflectionClass need string name or object.');
		}

		$class = StringNormalise::toClassNamespace($class);

		if (empty(static::$refs[$class]))
		{
			static::$refs[$class] = new \ReflectionClass($class);
		}

		return static::$refs[$class];
	}

	/**
	 * Get path from reflection.
	 *
	 * @param string|object $class The class or object to get reflection.
	 *
	 * @return  string The class file path.
	 */
	public static function getPath($class)
	{
		$ref = static::getReflection($class);

		return $ref->getFileName();
	}

	/**
	 * Call static magic method.
	 *
	 * @param string $name  The method name.
	 * @param array  $args  The arguments of this methods.
	 *
	 * @return  mixed  Return value from reflection class.
	 */
	public static function __callStatic($name, $args)
	{
		$class = array_shift($args);

		$ref = static::getReflection($class);

		return call_user_func_array(array($ref, $name), $args);
	}
}
