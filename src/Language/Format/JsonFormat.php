<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Language\Format;

use Symfony\Component\Yaml\Yaml;

/**
 * Class JsonFormat
 *
 * @since 1.0
 */
class JsonFormat extends AbstractFormat
{
	/**
	 * Property name.
	 *
	 * @var  string
	 */
	protected $name = 'json';

	/**
	 * parse
	 *
	 * @param string $string
	 *
	 * @return  string[]
	 */
	public function parse($string)
	{
		$array = json_decode($string);

		return $this->toOneDimension($array);
	}
}

