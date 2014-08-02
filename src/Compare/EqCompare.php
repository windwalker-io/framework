<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Compare;

/**
 * Class EqCompare
 *
 * @since 2.0
 */
class EqCompare extends Compare
{
	/**
	 * Operator symbol.
	 *
	 * @var  string
	 */
	protected $operator = '=';

	/**
	 * Do compare.
	 *
	 * @return  boolean  The result of compare.
	 */
	public function compare()
	{
		return ($this->compare1 == $this->compare2);
	}
}
