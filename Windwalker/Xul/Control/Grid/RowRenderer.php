<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Xul\Control\Grid;

use JGrid;
use JText;
use Windwalker\Data\Data;
use Windwalker\String\String;
use Windwalker\Helper\XmlHelper;
use Windwalker\Xul\AbstractXulRenderer;
use Windwalker\Xul\XulEngine;

/**
 * Class TableRenderer
 *
 * @since 1.0
 */
class RowRenderer extends AbstractXulRenderer
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
		$grid  = $data->xulControl->grid;
		$cells = static::renderChildren($engine, $element, $data);

		$grid->addRow(XmlHelper::getAttributes($element));

		foreach ($cells as $key => $cell)
		{
			$content = String::parseVariable($cell, $data);

			$attribs = XmlHelper::getAttributes($element);

			$grid->setRowCell($key, $content, $attribs);
		}

		reset($element);

		return $cells;
	}
}
