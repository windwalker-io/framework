<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Language\Format;

/**
 * Class IniFormat
 *
 * @since 2.0
 */
class PhpFormat extends AbstractFormat
{
	/**
	 * Property name.
	 *
	 * @var  string
	 */
	protected $name = 'php';

	/**
	 * parse
	 *
	 * @param array $array
	 *
	 * @return  array
	 */
	public function parse($array)
	{
		return $this->toOneDimension($array);
	}
}

