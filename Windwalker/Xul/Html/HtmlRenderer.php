<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Xul\Html;

use Windwalker\Helper\HtmlHelper;
use Windwalker\Helper\StringHelper;
use Windwalker\Helper\XmlHelper;
use Windwalker\Xul\AbstractXulRenderer;

/**
 * Class HtmlRenderer
 *
 * @since 1.0
 */
class HtmlRenderer extends AbstractXulRenderer
{
	/**
	 * doRender
	 *
	 * @param string            $name
	 * @param \SimpleXmlElement $element
	 * @param mixed             $data
	 *
	 * @return  mixed
	 */
	protected static function doRender($name, \SimpleXmlElement $element, $data)
	{
		$attributes = XmlHelper::getAttributes($element);

		$attributes = static::replaceVariable($attributes, $data);

		return HtmlHelper::buildTag($name, static::renderChildren($element, $data), $attributes);
	}
}
