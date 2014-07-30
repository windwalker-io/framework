<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Language\Format;

/**
 * Class IniFormat
 *
 * @since 1.0
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
 