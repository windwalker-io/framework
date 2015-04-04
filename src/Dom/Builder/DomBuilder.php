<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
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

		foreach ((array) $attribs as $key => $value)
		{
			if ($value === true)
			{
				$tag .= ' ' . $key;

				continue;
			}

			if ($value === null || $value === false)
			{
				continue;
			}

			$tag .= ' ' . $key . '=' . static::quote($value);
		}

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
