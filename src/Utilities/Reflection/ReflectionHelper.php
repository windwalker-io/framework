<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Utilities\Reflection;

/**
 * Reflection Helper
 *
 * @since 2.0
 */
class ReflectionHelper
{
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
		return new \ReflectionClass($class);
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
