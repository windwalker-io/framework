<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Language\Localise;

/**
 * Interface LocaliseInterface
 */
interface LocaliseInterface
{
	/**
	 * getPluralSuffixes
	 *
	 * @param int $count
	 *
	 * @return  string
	 */
	public function getPluralSuffix($count = 1);
}

