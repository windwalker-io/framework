<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Filter\Cleaner;

/**
 * Interface FilterRuleInterface
 *
 * @since  2.0
 */
interface CleanerInterface
{
	/**
	 * Method to clean text by rule.
	 *
	 * @param   string  $source  The source to be clean.
	 *
	 * @return  mixed  The cleaned value.
	 */
	public function clean($source);
}
