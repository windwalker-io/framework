<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
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
		'img', 'br', 'hr', 'area', 'param', 'wbr', 'base', 'link', 'meta', 'input', 'option', 'a', 'source'
	);

	/**
	 * Property trueValueMapping.
	 *
	 * @var  array
	 */
	protected static $trueValueMapping = array(
		'readonly' => 'readonly',
		'disabled' => 'disabled',
		'multiple' => 'true',
		'checked'  => 'checked',
		'selected' => 'selected'
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

	/**
	 * buildAttributes
	 *
	 * @param array $attribs
	 *
	 * @return  string
	 */
	public static function buildAttributes($attribs)
	{
		$attribs = static::mapAttrValues($attribs);

		return parent::buildAttributes($attribs);
	}

	/**
	 * mapAttrValues
	 *
	 * @param array $attribs
	 *
	 * @return  mixed
	 */
	protected static function mapAttrValues($attribs)
	{
		foreach (static::$trueValueMapping as $key => $value)
		{
			$attribs[$key] = !empty($attribs[$key]) ? $value : null;
		}

		return $attribs;
	}
}
