<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Dom;

/**
 * Html Elements collection.
 *
 * @since 2.0
 */
class DomElements extends \ArrayObject
{
	/**
	 * Convert all elements to string.
	 *
	 * @return  string
	 */
	public function __toString()
	{
		$return = '';

		foreach ($this as $element)
		{
			$return .= (string) $element;
		}

		return $return;
	}
}
