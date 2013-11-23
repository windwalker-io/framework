<?php
/**
 * @package     Windwalker.Framework
 * @subpackage  Helpers
 * @author      Simon Asika <asika32764@gmail.com>
 * @copyright   Copyright (C) 2013 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * A Loader for WindWalker, but not recommended to use now.
 *
 * @package     Windwalker.Framework
 * @subpackage  Helpers
 */
class AKHelperLoader
{
	/**
	 * Cahche files' path, do not include twice.
	 *
	 * @var array
	 */
	public static $files = array();

	/**
	 * Import a file by URI.
	 * Example: "site://models/items" AS ROOT/components/com_component/models/items.php
	 * <br /> OR "admin://includes/plugins/pro/pro" AS ROOT/administrator/components/com_component/includes/plugins/pro/pro.php
	 *
	 * @param   string  The file URI.
	 * @param   string  Component option, eg: com_content.
	 *
	 * @return  boolean Include success or not.
	 */
	public static function import($uri, $option = null)
	{
		$key = $uri;
		if (isset(self::$files[$key]))
		{
			return true;
		}

		$uri  = explode('://', $uri);
		$root = AKHelper::_('path.get', $uri[0], $option);
		$path = $root . '/' . $uri[1] . '.php';

		if (JFile::exists($path))
		{
			include_once $path;
			self::$files[$key] = $path;

			return true;
		}
		else
		{
			return false;
		}
	}
}