<?php
/**
 * Part of joomla321 project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Helper;

/**
 * Class AssetHelper
 *
 * @since 1.0
 */
class AssetHelper
{
	/**
	 * addCss
	 *
	 * @param string $file
	 * @param string $category
	 * @param array  $attribs
	 *
	 * @return void
	 */
	public static function addCss($file, $category = null, $attribs = array())
	{
		if ($category)
		{
			$file = $category . '/' . $file;
		}

		\JHtml::stylesheet($file, $attribs, true);
	}
}
