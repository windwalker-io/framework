<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Dom\Builder;

/**
 * HTML Builder helper.
 *
 * @since 2.0
 */
class HtmlBuilder extends DomBuilder
{
	/**
	 * Unpaired elements.
	 *
	 * @var  array
	 */
	protected static $unpairedElements = array(
		'img', 'br', 'hr', 'area', 'param', 'wbr', 'base', 'link', 'meta', 'input', 'option'
	);

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
		$paired = $forcePair ? : !in_array(strtolower($name), static::$unpairedElements);

		return parent::create($name, $content, $attribs, $paired);
	}
}
