<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Xul\Control;

use Windwalker\Helper\XmlHelper;
use Windwalker\Html\HtmlElement;

/**
 * Class DumpRenderer
 *
 * @since 1.0
 */
class DumpRenderer extends CallRenderer
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
		$element['static'] = $handler = XmlHelper::get($element, 'handler', 'print_r');

		$element->addChild('argument')
			->addAttribute('data', XmlHelper::get($element, 'data'));

		$element->addChild('argument', 1);

		if ($handler == 'print_r')
		{
			return new HtmlElement('pre', parent::doRender($name, $element, $data));
		}
		else
		{
			return parent::doRender($name, $element, $data);
		}
	}
}
