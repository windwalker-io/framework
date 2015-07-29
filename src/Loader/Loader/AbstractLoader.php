<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Loader\Loader;

/**
 * AbstractLoader
 */
abstract class AbstractLoader
{
	/**
	 * Installs this class loader on the SPL autoload stack.
	 *
	 * @return Psr0Loader
	 */
	public function register()
	{
		spl_autoload_register(array($this, 'loadClass'));

		return $this;
	}

	/**
	 * Uninstalls this class loader from the SPL autoloader stack.
	 *
	 * @return Psr0Loader
	 */
	public function unregister()
	{
		spl_autoload_unregister(array($this, 'loadClass'));

		return $this;
	}

	/**
	 * Loads the given class or interface.
	 *
	 * @param string $className The name of the class to load.
	 *
	 * @return Psr0Loader
	 */
	abstract public function loadClass($className);

	/**
	 * normalizeClass
	 *
	 * @param string $class
	 *
	 * @return  string
	 */
	public static function normalizeClass($class)
	{
		$class = trim($class, '\\');

		return $class;
	}

	/**
	 * normalizePath
	 *
	 * @param string $path
	 * @param bool   $endSlash
	 *
	 * @return  string
	 */
	public static function normalizePath($path, $endSlash = true)
	{
		$path = rtrim($path, '/') . DIRECTORY_SEPARATOR;
		$path = rtrim($path, DIRECTORY_SEPARATOR);

		$path = $endSlash ? $path . DIRECTORY_SEPARATOR : $path;

		return $path;
	}

	/**
	 * If a file exists, require it from the file system.
	 *
	 * @param string $file The file to require.
	 *
	 * @return static
	 */
	protected function requireFile($file)
	{
		require $file;

		return $this;
	}
}

