<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Compare;

/**
 * Class NotinCompare
 *
 * @since 1.0
 */
class NotinCompare extends InCompare
{
	/**
	 * Property operator.
	 *
	 * @var  string
	 */
	protected $operator = 'NOT IN';

	/**
	 * compare
	 *
	 * @return  mixed
	 */
	public function compare()
	{
		return !parent::compare();
	}
}
