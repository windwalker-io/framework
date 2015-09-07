<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Dom\Builder;

/**
 * Class XmlBuilder
 *
 * @since 2.0
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

		$tag .= static::buildAttributes($attribs);

		if ($content !== null)
		{
			$tag .= '>' . $content . '</' . $name . '>';
		}
		else
		{
			$tag .= $forcePair ? '></' . $name . '>' : ' />';
		}

		return $tag;
	}

	/**
	 * buildAttributes
	 *
	 * @param array $attribs
	 *
	 * @return  string
	 */
	public static function buildAttributes($attribs)
	{
		$string = '';

		foreach ((array) $attribs as $key => $value)
		{
			if ($value === true)
			{
				$string .= ' ' . $key;

				continue;
			}

			if ($value === null || $value === false)
			{
				continue;
			}

			$string .= ' ' . $key . '=' . static::quote($value);
		}

		return $string;
	}

	/**
	 * quote
	 *
	 * @param string $value
	 *
	 * @return  string
	 */
	public static function quote($value)
	{
		return '"' . $value . '"';
	}
}
