<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Language\Format;

/**
 * Class IniFormat
 *
 * @since 2.0
 */
class IniFormat extends AbstractFormat
{
	/**
	 * Property name.
	 *
	 * @var  string
	 */
	protected $name = 'ini';

	/**
	 * parse
	 *
	 * @param string $string
	 *
	 * @return  array
	 */
	public function parse($string)
	{
		return parse_ini_string($string);
	}
}

