<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Xul\Html;

use Windwalker\Html\HtmlBuilder;
use Windwalker\Helper\XmlHelper;
use Windwalker\Html\HtmlElement;
use Windwalker\Xul\AbstractXulRenderer;
use Windwalker\Xul\XulEngine;

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
	 * @param XulEngine         $engine
	 * @param \SimpleXmlElement $element
	 * @param mixed             $data
	 *
	 * @throws \LogicException
	 * @return  mixed
	 */
	protected static function doRender($name, XulEngine $engine, \SimpleXmlElement $element, $data)
	{
		$attributes = XmlHelper::getAttributes($element);

		return new HtmlElement($name, static::renderChildren($engine, $element, $data), $attributes);
	}
}
