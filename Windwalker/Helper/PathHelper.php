<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Helper;

/**
 * Class PathHelper
 *
 * @since 1.0
 */
class PathHelper
{
	/**
	 * Property extMapper.
	 *
	 * @var array
	 */
	protected static $extMapper = array(
		'com_' => 'components',
		'mod_' => 'modules',
		'plg_' => 'plugins',
		'lib_' => 'libraries',
		'tpl_' => 'templates'
	);

	/**
	 * get
	 *
	 * @param string $element
	 * @param string $client
	 * @param bool   $absolute
	 *
	 * @return string
	 */
	public static function get($element, $client = null, $absolute = true)
	{
		$element = strtolower($element);

		list($extension, $name, $group) = static::extractElement($element);

		$folder = $name;

		// Assign name path.
		switch ($extension)
		{
			case 'components':
			case 'modules':
				$folder = $element;
				break;

			case 'plugins':
				$folder = $group . '/' . $name;
				$client = 'site';
				break;

			case 'libraries':
				$client = 'site';
				break;

			default:
				$folder = $name;
				break;
		}

		// Build path
		$path = $extension . '/' . $folder;

		if (!$absolute)
		{
			return $path;
		}

		// Add absolute path.
		switch ($client)
		{
			case 'site':
				$path = JPATH_SITE . '/' . $path;
				break;

			case 'admin':
			case 'administrator':
				$path = JPATH_ADMINISTRATOR . '/' . $path;
				break;

			default:
				$path = JPATH_BASE . '/' . $path;
				break;
		}

		return $path;
	}

	/**
	 * getAdmin
	 *
	 * @param string $element
	 * @param bool   $absolute
	 *
	 * @return string
	 */
	public static function getAdmin($element, $absolute = true)
	{
		return static::get($element, 'administrator', $absolute);
	}

	/**
	 * getAdmin
	 *
	 * @param string $element
	 * @param bool   $absolute
	 *
	 * @return string
	 */
	public static function getSite($element, $absolute = true)
	{
		return static::get($element, 'site', $absolute);
	}

	/**
	 * extractElement
	 *
	 * @param string $element
	 *
	 * @return array
	 *
	 * @throws \InvalidArgumentException
	 */
	protected static function extractElement($element)
	{
		$prefix = substr($element, 0, 4);

		$ext = static::getExtName($prefix);

		if (!$ext)
		{
			throw new \InvalidArgumentException(sprintf('Need extension prefix, "%s" given.', $element));
		}

		$group = '';
		$name = substr($element, 4);

		// Get group
		if ($ext == 'plugins')
		{
			$name  = explode('_', $name);

			$group = array_shift($name);

			$name  = implode('_', $name);

			if (!$name)
			{
				throw new \InvalidArgumentException(sprintf('Plugin name need group, eg: "plg_group_name", "%s" given.', $element));
			}
		}

		return array($ext, $name, $group);
	}

	/**
	 * getExtName
	 *
	 * @param string $prefix
	 *
	 * @return string|null
	 */
	protected static function getExtName($prefix)
	{
		if (!empty(static::$extMapper[$prefix]))
		{
			return static::$extMapper[$prefix];
		}

		return null;
	}
}
