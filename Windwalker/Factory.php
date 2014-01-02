<?php
/**
 * Part of joomla321 project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker;

/**
 * Class Factory
 *
 * @since 1.0
 */
class Factory
{
	/**
	 * getDate
	 *
	 * @param string $date
	 * @param bool   $locale
	 *
	 * @return \JDate
	 */
	public function getDate($date = 'now', $locale = true)
	{
		$tz = \JFactory::getConfig()->get('offset');

		return \JFactory::getDate($date, $tz);
	}
}
