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
use Windwalker\Xul\XulEngine;
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
	 * @param XulEngine         $engine
	 * @param \SimpleXmlElement $element
	 * @param mixed             $data
	 *
	 * @throws \LogicException
	 * @return  mixed
	 */
	protected static function doRender($name, XulEngine $engine, \SimpleXmlElement $element, $data)
	{
		if (!isset($data->view->colSpan))
		{
			throw new \LogicException('Please put "column" tags in "row" tag.');
		}

		$span = XmlHelper::get($element, 'span');
		$fill = XmlHelper::getBool($element, 'fill', !((boolean) $span));

		if (!$span)
		{
			$span = 12;
		}

		if ($fill)
		{
			$span = $data->view->colSpan ? : 12;
		}
		else
		{
			$data->view->colSpan -= $span;
		}

		if (((int) $span) <= 0)
		{
			$span = 12;
		}

		if ($data->view->colSpan <= 0)
		{
			$data->view->colSpan = 12 + $data->view->colSpan;
		}

		$colClass = 'span' . $span;

		$element['class'] = isset($element['class']) ? $colClass . ' ' . $element['class'] : $colClass;

		return HtmlRenderer::render('div', $engine, $element, $data);
	}
}
