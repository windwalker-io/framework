<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Html;

use Windwalker\String\String;

// No direct access
defined('_JEXEC') or die;

/**
 * HTML Builder helper.
 *
 * @package     Windwalker.Framework
 * @subpackage  Helpers
 */
class HtmlBuilder
{
	/**
	 * @var  array  Property unpairedElements.
	 */
	protected static $unpairedElements = array(
		'img', 'br', 'hr', 'area', 'param', 'wbr', 'base', 'link', 'meta', 'input', 'option'
	);

	/**
	 * create
	 *
	 * @param string $name
	 * @param mixed  $content
	 * @param array  $attribs
	 *
	 * @return  string
	 */
	public static function create($name, $content = '', $attribs = array())
	{
		$name = trim($name);

		$unpaired = in_array(strtolower($name), static::$unpairedElements);

		$tag = '<' . $name;

		foreach ((array) $attribs as $key => $value)
		{
			if ($value !== null && $value !== false && $value !== '')
			{
				$tag .= ' ' . $key . '=' . String::quote($value, '""');
			}
		}

		if ($content)
		{
			if (!($content instanceof HtmlElement))
			{
				$content = implode(PHP_EOL, (array) $content);
			}

			$tag .= '>' . PHP_EOL . "\t" . $content . PHP_EOL . '</' . $name . '>';
		}
		else
		{
			$tag .= $unpaired ? ' />' : '></' . $name . '>';
		}

		return $tag;
	}
}
