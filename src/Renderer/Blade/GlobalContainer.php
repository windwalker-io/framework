<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2015 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Renderer\Blade;

/**
 * The GlobalContainer class.
 *
 * @since  {DEPLOY_VERSION}
 */
abstract class GlobalContainer
{
	/**
	 * Property compilers.
	 *
	 * @var  callable[]
	 */
	protected static $compilers = array();

	/**
	 * addCompiler
	 *
	 * @param string    $name
	 * @param callable  $compiler
	 *
	 * @return  void
	 */
	public static function addCompiler($name, $compiler)
	{
		if (!is_callable($compiler))
		{
			throw new \InvalidArgumentException('Compiler should be callable.');
		}

		static::$compilers[$name] = $compiler;
	}

	/**
	 * getCompiler
	 *
	 * @param   string $name
	 *
	 * @return  callable
	 */
	public static function getCompiler($name)
	{
		if (!empty(static::$compilers[$name]))
		{
			return static::$compilers[$name];
		}

		return null;
	}

	/**
	 * removeCompiler
	 *
	 * @param string $name
	 *
	 * @return  void
	 */
	public static function removeCompiler($name)
	{
		if (isset(static::$compilers[$name]))
		{
			unset(static::$compilers[$name]);
		}
	}

	/**
	 * Method to get property Compilers
	 *
	 * @return  callable[]
	 */
	public static function getCompilers()
	{
		return static::$compilers;
	}

	/**
	 * Method to set property extensions
	 *
	 * @param   callable[] $compilers
	 *
	 * @return  void
	 */
	public static function setCompilers(array $compilers)
	{
		static::$compilers = $compilers;
	}
}
