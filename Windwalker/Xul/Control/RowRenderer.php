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
class RowRenderer extends AbstractXulRenderer
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
		$rowClass = XmlHelper::getBool($element, 'fluid', true) ? 'row-fluid' : 'row';

		$element['class'] = isset($element['class']) ? $rowClass . ' ' . $element['class'] : $rowClass;

		return HtmlRenderer::render('div', $element, $data);
	}
}
