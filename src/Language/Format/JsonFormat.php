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
 * Class JsonFormat
 *
 * @since {DEPLOY_VERSION}
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

