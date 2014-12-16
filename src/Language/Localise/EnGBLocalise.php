<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Language\Localise;

/**
 * Class EnGBLocalise
 *
 * @since 2.0
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
