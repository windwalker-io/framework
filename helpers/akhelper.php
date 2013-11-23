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
 * AKHelper base class.
 *
 * @package     Windwalker.Framework
 * @subpackage  Helpers
 */
class AKHelper extends AKProxy
{
	/**
	 * An singleton pattern array to store Config JRegistry instance.
	 *
	 * @var array
	 */
	static $config = array();

	/**
	 * Store component version to get.
	 *
	 * @var integer
	 */
	static $version = 0;

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return   JObject
	 */
	public static function getActions($option)
	{
		$user   = JFactory::getUser();
		$result = new JObject;

		$assetName = $option;

		$actions = array(
			'core.admin',
			'core.manage',
			'core.create',
			'core.edit',
			'core.edit.own',
			'core.edit.state',
			'core.delete'
		);

		foreach ($actions as $action)
		{
			$result->set($action, $user->authorise($action, $assetName));
		}

		return $result;
	}

	/**
	 * Print Array or Object as tree node. If send multiple params in this method, will batch print it.
	 *
	 * @param    mixed $data Array or Object to print.
	 */
	public static function show($data)
	{
		$args = func_get_args();

		// Print Multiple values
		if (count($args) > 1)
		{
			$prints = array();

			$i = 1;

			foreach ($args as $arg)
			{
				$prints[] = "[Value " . $i . "]\n" . print_r($arg, 1);
				$i++;
			}

			echo '<pre>' . implode("\n\n", $prints) . '</pre>';
		}
		else
		{
			// Print one value.
			echo '<pre>' . print_r($data, 1) . '</pre>';
		}
	}

	/**
	 * Detect is this page are frontpage?
	 *
	 * @return    boolean    Is frontpage?
	 */
	public static function isHome()
	{
		$juri        = JFactory::getURI();
		$current_url = $juri->toString();

		if ($juri->base() == $current_url || $juri->base() . 'index.php' == $current_url)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Get Component params. This is a proxy for AKHelperSystem::getParams.
	 *
	 * @param    string $option Component element name, eg: com_extension.
	 *
	 * @return   JRegistry  A JRegistry object.
	 */
	public static function getParams($option = null)
	{
		return AKHelper::_('system.getParams', $option);
	}
}

if (!class_exists('AK'))
{
	/**
	 * An alias for AKHelper base class.
	 *
	 * @package     Windwalker.Framework
	 * @subpackage  Helpers
	 */
	class AK extends AKHelper
	{
	}
}
