<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Loader\Loader;

/**
 * Class Psr4Loader
 *
 * @note This class based on PHP-FIG example class.
 *       See: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader-examples.md
 *
 * @since 1.0
 */
class Psr4Loader
{
	/**
	 * An associative array where the key is a namespace prefix and the value
	 * is an array of base directories for classes in that namespace.
	 *
	 * @var array
	 */
	protected $prefixes = array();

	/**
	 * Register loader with SPL autoloader stack.
	 *
	 * @return Psr4Loader
	 */
	public function register()
	{
		spl_autoload_register(array($this, 'loadClass'));

		return $this;
	}

	/**
	 * Adds a base directory for a namespace prefix.
	 *
	 * @param string $prefix   The namespace prefix.
	 * @param string $base_dir A base directory for class files in the
	 *                         namespace.
	 * @param bool   $prepend  If true, prepend the base directory to the stack
	 *                         instead of appending it; this causes it to be searched first rather
	 *                         than last.
	 *
	 * @return Psr4Loader
	 */
	public function addNamespace($prefix, $base_dir, $prepend = false)
	{
		// normalize namespace prefix
		$prefix = trim($prefix, '\\') . '\\';

		// normalize the base directory with a trailing separator
		$base_dir = rtrim($base_dir, '/') . DIRECTORY_SEPARATOR;
		$base_dir = rtrim($base_dir, DIRECTORY_SEPARATOR) . '/';

		// initialize the namespace prefix array
		if (isset($this->prefixes[$prefix]) === false)
		{
			$this->prefixes[$prefix] = array();
		}

		// retain the base directory for the namespace prefix
		if ($prepend)
		{
			array_unshift($this->prefixes[$prefix], $base_dir);
		}
		else
		{
			array_push($this->prefixes[$prefix], $base_dir);
		}

		return $this;
	}

	/**
	 * Loads the class file for a given class name.
	 *
	 * @param string $class The fully-qualified class name.
	 *
	 * @return Psr4Loader
	 */
	public function loadClass($class)
	{
		// the current namespace prefix
		$prefix = $class;

		// work backwards through the namespace names of the fully-qualified
		// class name to find a mapped file name
		while (false !== $pos = strrpos($prefix, '\\'))
		{
			// retain the trailing namespace separator in the prefix
			$prefix = substr($class, 0, $pos + 1);

			// the rest is the relative class name
			$relative_class = substr($class, $pos + 1);

			// try to load a mapped file for the prefix and relative class
			$mapped_file = $this->loadMappedFile($prefix, $relative_class);

			if ($mapped_file)
			{
				return $mapped_file;
			}

			// remove the trailing namespace separator for the next iteration
			// of strrpos()
			$prefix = rtrim($prefix, '\\');
		}

		return $this;
	}

	/**
	 * Load the mapped file for a namespace prefix and relative class.
	 *
	 * @param string $prefix         The namespace prefix.
	 * @param string $relative_class The relative class name.
	 *
	 * @return Psr4Loader
	 */
	protected function loadMappedFile($prefix, $relative_class)
	{
		// are there any base directories for this namespace prefix?
		if (isset($this->prefixes[$prefix]) === false)
		{
			return false;
		}

		// look through base directories for this namespace prefix
		foreach ($this->prefixes[$prefix] as $base_dir)
		{
			// replace namespace separators with directory separators
			// in the relative class name, append with .php
			$file = $base_dir
				. str_replace('\\', DIRECTORY_SEPARATOR, $relative_class)
				. '.php';

			// if the mapped file exists, require it
			$this->requireFile($file);
		}

		return $this;
	}

	/**
	 * If a file exists, require it from the file system.
	 *
	 * @param string $file The file to require.
	 *
	 * @return bool True if the file exists, false if not.
	 */
	protected function requireFile($file)
	{
		if (file_exists($file))
		{
			require $file;

			return true;
		}

		return false;
	}
}
 