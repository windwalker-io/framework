<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Compare;

/**
 * Class LteCompare
 *
 * @since 1.0
 */
class LteCompare extends Compare
{
	/**
	 * Property operator.
	 *
	 * @var  string
	 */
	protected $operator = '<=';

	/**
	 * compare
	 *
	 * @return  mixed
	 */
	public function compare()
	{
		return ($this->compare1 <= $this->compare2);
	}
}
