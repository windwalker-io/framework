<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Compare;

/**
 * Class NotinCompare
 *
 * @since 2.0
 */
class NotinCompare extends InCompare
{
	/**
	 * Operator symbol.
	 *
	 * @var  string
	 */
	protected $operator = 'NOT IN';

	/**
	 * Do compare.
	 *
	 * @return  boolean  The result of compare.
	 */
	public function compare()
	{
		return !parent::compare();
	}
}
