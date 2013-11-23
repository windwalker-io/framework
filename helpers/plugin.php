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
 * To include component inner-plugin.
 * All inner-plugin put in [component]/includes/plugins.
 *
 * @package     Windwalker.Framework
 * @subpackage  Helpers
 */
class AKHelperPlugin
{
	/**
	 * Version means plugin name. If plugin "pro" exists, verion pro exists.
	 *
	 * @var array
	 */
	protected static $version = array();

	/**
	 * Store all plugins.
	 *
	 * @var array
	 */
	protected static $plugins = array();

	/**
	 * Is a plugin exists. Return True or False.
	 *
	 * @param   string $version Plugin name.
	 *
	 * @return  boolean Plugin exists or not.
	 */
	public static function get($version)
	{
		if (!empty(self::$version[$version]))
		{
			return true;
		}

		return false;
	}

	/**
	 * Attach all inner-plugins.
	 */
	public static function attachPlugins()
	{
		jimport('joomla.filesystem.folder');
		$plugins = JFolder::folders(AKHelper::_('path.getAdmin') . '/includes/plugins');

		if (!$plugins)
		{
			$plugins = array();
		}

		foreach ($plugins as $plugin)
		{
			self::attachPlugin($plugin);
		}
	}

	/**
	 * Attach one inner-plugin.
	 *
	 * @param   string $name Plugin name.
	 *
	 * @return  boolean Attach success or not.
	 */
	public static function attachPlugin($name)
	{
		$app              = JFactory::getApplication();
		$path             = AKHelper::_('path.getAdmin') . "/includes/plugins/{$name}/{$name}.php";
		$option           = JRequest::getVar('option');
		$config['params'] = JComponentHelper::getParams($option);

		if (JFile::exists($path))
		{
			include_once $path;
			$dispatcher = JDispatcher::getInstance();

			$class_name = 'plg' . ucfirst(str_replace('com_', '', $option)) . ucfirst($name);

			if (class_exists($class_name))
			{

				$plugin = new $class_name($dispatcher, $config);
				$dispatcher->attach($plugin);

				self::$plugins[$name] = $plugin;
				self::$version[$name] = true;

				return true;
			}
			else
			{
				return false;
			}

		}
		else
		{
			return false;
		}

	}
}
