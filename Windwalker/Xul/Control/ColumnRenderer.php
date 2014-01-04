<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Xul\Control;

use Windwalker\Helper\XmlHelper;
use Windwalker\Xul\AbstractXulRenderer;
use Windwalker\Xul\Html\HtmlRenderer;

/**
 * Class ColumnRenderer
 *
 * @since 1.0
 */
class ColumnRenderer extends AbstractXulRenderer
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
		$colClass = 'span' . XmlHelper::get($element, 'span', '12');

		$element['class'] = isset($element['class']) ? $colClass . ' ' . $element['class'] : $colClass;

		return HtmlRenderer::render('div', $element, $data);
	}
}
