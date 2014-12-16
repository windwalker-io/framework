<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Language\Format;

use Symfony\Component\Yaml\Yaml;

/**
 * Class IniFormat
 *
 * @since 2.0
 */
class YamlFormat extends AbstractFormat
{
	/**
	 * Property name.
	 *
	 * @var  string
	 */
	protected $name = 'yaml';

	/**
	 * parse
	 *
	 * @param string $string
	 *
	 * @return  string[]
	 */
	public function parse($string)
	{
		$array = Yaml::parse($string);

		return $this->toOneDimension($array);
	}
}

