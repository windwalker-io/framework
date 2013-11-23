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
 * Some useful function for Joomla! Content Component.
 *
 * @package     Windwalker.Framework
 * @subpackage  Helpers
 */
class AKHelperJcontent
{

	/**
	 * Get article link url by slug.
	 *
	 * @param   string  $slug     The id slug, eg: "43:artile-alias"
	 * @param   string  $catslug  The category slug, eg: "12:category-alias", can only include number.
	 * @param   boolean $absolute Ture to return whole absolute url.
	 *
	 * @return  type
	 */
	public static function getArticleLink($slug, $catslug = null, $absolute = 0)
	{

		include_once JPATH_ROOT . DS . 'components' . DS . 'com_content' . DS . 'helpers' . DS . 'route.php';

		$path = JRoute::_(ContentHelperRoute::getArticleRoute($slug, $catslug));
		$host = str_replace('http://' . $_SERVER['HTTP_HOST'], '', JURI::root());
		if ($host != '/')
		{
			$path = str_replace($host, '', $path);
		}

		if ($absolute)
		{
			return AKHelper::_('uri.pathAddHost', $path);
		}
		else
		{
			return $path;
		}

	}

	/**
	 * Get category link url by category id.
	 *
	 * @param   integer $catid    Category id to load Table.
	 * @param   boolean $absolute Ture to return whole absolute url.
	 *
	 * @return  type
	 */
	public static function getCategoryLink($catid, $absolute = 0)
	{

		include_once JPATH_ROOT . DS . 'components' . DS . 'com_content' . DS . 'helpers' . DS . 'route.php';

		$path = JRoute::_(ContentHelperRoute::getCategoryRoute($catid));
		$host = str_replace('http://' . $_SERVER['HTTP_HOST'], '', JURI::root());
		$path = str_replace($host, '', $path);

		if ($absolute)
		{
			return AKHelper::_('uri.pathAddHost', $path);
		}
		else
		{
			return $path;
		}
	}
}


