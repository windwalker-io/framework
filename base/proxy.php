<?php
/**
 * @package     Windwalker.Framework
 * @subpackage  Base
 * @author      Simon Asika <asika32764@gmail.com>
 * @copyright   Copyright (C) 2013 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * A base proxy class for AKHelper to call any sub helpers.
 *
 * @package     Windwalker.Framework
 * @subpackage  Base
 */
class AKProxy
{

	/**
	 * An array to hold included paths
	 *
	 * @var    array
	 */
	protected static $includePaths = array();

	/**
	 * An array to hold method references
	 *
	 * @var    array
	 */
	protected static $registry = array();

	/**
	 * A default prefix when method not exists, call this class instead.
	 *
	 * @var    string
	 */
	protected static $prefix = 'AKHelper';

	/**
	 * Method to extract a key
	 *
	 * @param   string  $key  The name of helper method to load, (prefix).(class).function
	 *                        prefix and class are optional and can be used to load custom html helpers.
	 *
	 * @return  array  Contains lowercase key, prefix, file, function.
	 */
	protected static function extract($key)
	{
		$key = preg_replace('#[^A-Z0-9_\.]#i', '', $key);

		// Check to see whether we need to load a helper file
		$parts  = explode('.', $key);
		$file   = '';
		$prefix = '';

		if (count($parts) == 3)
		{
			$prefix = array_shift($parts);
			$file   = array_shift($parts);
		}
		elseif (count($parts) == 2)
		{
			$prefix = self::$prefix;
			$file   = array_shift($parts);
		}
		else
		{
			$prefix = self::$prefix;
		}

		$func = array_shift($parts);

		return array(strtolower($key), $prefix, $file, $func);
	}

	/**
	 * Class loader method
	 * Additional arguments may be supplied and are passed to the sub-class.
	 * Additional include paths are also able to be specified for third-party use
	 *
	 * @param   string  $key  The name of helper method to load, (prefix).(class).function
	 *                        prefix and class are optional and can be used to load custom
	 *                        html helpers.
	 *
	 * @return  mixed  self::call($function, $args) or False on error
	 */
	public static function _($key)
	{

		list($key, $prefix, $file, $func) = self::extract($key);

		if (array_key_exists($key, self::$registry))
		{
			$function = self::$registry[$key];
			$args     = func_get_args();
			// Remove function name from arguments
			array_shift($args);

			return self::call($function, $args);
		}

		$className = $prefix . ucfirst($file);

		if (!class_exists($className))
		{
			jimport('joomla.filesystem.path');

			if ($path = JPath::find(self::$includePaths[$prefix], strtolower($file) . '.php'))
			{
				require_once $path;

				/*
				if (!class_exists($className))
				{
					//JError::raiseError(500, JText::sprintf('JLIB_HTML_ERROR_NOTFOUNDINFILE', $className, $func));
					//return false;
				}
				*/
			}
		}

		$toCall = array($className, $func);

		if (is_callable($toCall))
		{
			self::register($key, $toCall);
			$args = func_get_args();

			// Remove function name from arguments
			array_shift($args);

			return self::call($toCall, $args);
		}
		elseif ($prefix != 'AKHelper')
		{
			$args    = func_get_args();
			$args[0] = 'AKHelper.' . $file . '.' . $func;

			return call_user_func_array(array('AKHelper', '_'), $args);
		}
		else
		{
			JError::raiseWarning(500, JText::sprintf('JLIB_HTML_ERROR_NOTSUPPORTED', $className, $func));

			return false;
		}
	}

	/**
	 * Registers a function to be called with a specific key
	 *
	 * @param   string  $key       The name of the key
	 * @param   string  $function  Function or method
	 *
	 * @return  boolean  True if the function is callable
	 */
	public static function register($key, $function)
	{
		list($key) = self::extract($key);

		if (is_callable($function))
		{
			self::$registry[$key] = $function;

			return true;
		}

		return false;
	}

	/**
	 * Removes a key for a method from registry.
	 *
	 * @param   string  $key  The name of the key
	 *
	 * @return  boolean  True if a set key is unset
	 */
	public static function unregister($key)
	{
		list($key) = self::extract($key);

		if (isset(self::$registry[$key]))
		{
			unset(self::$registry[$key]);

			return true;
		}

		return false;
	}

	/**
	 * Test if the key is registered.
	 *
	 * @param   string  $key  The name of the key
	 *
	 * @return  boolean  True if the key is registered.
	 */
	public static function isRegistered($key)
	{
		list($key) = self::extract($key);

		return isset(self::$registry[$key]);
	}

	/**
	 * Function caller method
	 *
	 * @param   string $function Function or method to call
	 * @param   array  $args     Arguments to be passed to function
	 *
	 * @return  mixed   Function result or false on error.
	 * @see     http://php.net/manual/en/function.call-user-func-array.php
	 */
	protected static function call($function, $args)
	{
		if (is_callable($function))
		{
			// PHP 5.3 workaround
			$temp = array();

			foreach ($args as &$arg)
			{
				$temp[] = & $arg;
			}

			return call_user_func_array($function, $temp);
		}
		else
		{
			JError::raiseError(500, JText::_('JLIB_HTML_ERROR_FUNCTION_NOT_SUPPORTED'));

			return false;
		}
	}

	/**
	 * Add a directory where self should search for helpers. You may
	 * either pass a string or an array of directories.
	 *
	 * @param   string  $path    A path to search.
	 * @param   null    $prefix  Prefix.
	 *
	 * @return  array  An array with directory elements
	 */
	public static function addIncludePath($path = '', $prefix = null)
	{
		// Force path to array
		settype($path, 'array');

		$prefix = $prefix ? $prefix : self::getPrefix();

		if (!isset(self::$includePaths[$prefix]))
		{
			self::$includePaths[$prefix] = array();
		}

		// Loop through the path directories
		foreach ($path as $dir)
		{
			if (!empty($dir) && !in_array($dir, self::$includePaths[$prefix]))
			{
				jimport('joomla.filesystem.path');
				array_unshift(self::$includePaths[$prefix], JPath::clean($dir));
			}
		}

		return self::$includePaths[$prefix];
	}

	/**
	 * Set current sub class prefix.
	 *
	 * @param   string  $prefix  A helper name for component.
	 *
	 * @return  void
	 */
	public static function setPrefix($prefix)
	{
		self::$prefix                = $prefix;
		self::$includePaths[$prefix] = array();
	}

	/**
	 * Get current prefix.
	 *
	 * @return  string  Current helper name.
	 */
	public static function getPrefix()
	{
		return self::$prefix;
	}

	/**
	 * A debug tool.
	 *
	 * @param   string  $var  Get var.
	 *
	 * @return  mixed
	 */
	public static function get($var)
	{
		return self::$var;
	}
}
