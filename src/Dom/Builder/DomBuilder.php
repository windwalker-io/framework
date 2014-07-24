<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Dom\Builder;

/**
 * Class XmlBuilder
 *
 * @since 1.0
 */
class DomBuilder
{
	/**
	 * Create a html element.
	 *
	 * @param string $name      Element tag name.
	 * @param mixed  $content   Element content.
	 * @param array  $attribs   Element attributes.
	 * @param bool   $forcePair Force pair it.
	 *
	 * @return  string Created element string.
	 */
	public static function create($name, $content = '', $attribs = array(), $forcePair = false)
	{
		$name = trim($name);

		$tag = '<' . $name;

		foreach ((array) $attribs as $key => $value)
		{
			if ($value !== null && $value !== false && $value !== '')
			{
				$tag .= ' ' . $key . '=' . static::quote($value);
			}
		}

		if ($content)
		{
			$tag .= '>' . PHP_EOL . "\t" . $content . PHP_EOL . '</' . $name . '>';
		}
		else
		{
			$tag .= $forcePair ? '></' . $name . '>' : ' />';
		}

		return $tag;
	}

	/**
	 * quote
	 *
	 * @param string $value
	 *
	 * @return  string
	 */
	public function quote($value)
	{
		return '"' . $value . '"';
	}
}
 