<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Language\Localise;

/**
 * Class EnGBLocalise
 *
 * @since 1.0
 */
class EnGBLocalise implements LocaliseInterface
{
	/**
	 * getPluralSuffixes
	 *
	 * @param int $count
	 *
	 * @return  string
	 */
	public function getPluralSuffix($count = 1)
	{
		if ($count == 0)
		{
			return '0';
		}
		elseif ($count == 1)
		{
			return '';
		}

		return 'more';
	}
}
 