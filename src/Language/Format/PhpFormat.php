<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Language\Format;

/**
 * Class IniFormat
 *
 * @since {DEPLOY_VERSION}
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

