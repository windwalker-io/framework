<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Helper;

/**
 * Class JContentHelper
 *
 * @since 1.0
 */
class JContentHelper
{
	/**
	 * Get article link url by slug.
	 *
	 * @param  string  $slug     The id slug, eg: "43:artile-alias"
	 * @param  string  $catslug  The category slug, eg: "12:category-alias", can only include number.
	 * @param  bool    $absolute Ture to return whole absolute url.
	 *
	 * @return string Article link url.
	 */
	public static function getArticleLink($slug, $catslug = null, $absolute = false)
	{
		include_once JPATH_ROOT . '/components/com_content/helpers/route.php';

		$path = \ContentHelperRoute::getArticleRoute($slug, $catslug);

		if ($absolute)
		{
			return \JUri::root() . $path;
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
	 * @param   bool    $absolute Ture to return whole absolute url.
	 *
	 * @return  string Category link url.
	 */
	public static function getCategoryLink($catid, $absolute = false)
	{
		include_once JPATH_ROOT . '/components/com_content/helpers/route.php';

		$path = \ContentHelperRoute::getCategoryRoute($catid);

		if ($absolute)
		{
			return \JUri::root() . $path;
		}
		else
		{
			return $path;
		}
	}
}
