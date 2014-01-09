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
use Windwalker\Helper\StringHelper;
use Windwalker\Helper\XmlHelper;
use Windwalker\Xul\AbstractXulRenderer;

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
	 * @param \SimpleXmlElement $element
	 * @param mixed             $data
	 *
	 * @return  mixed
	 */
	protected static function doRender($name, \SimpleXmlElement $element, $data)
	{
		$grid  = $data->xulControl->grid;
		$cells = static::renderChildren($element, $data);

		$grid->addRow(static::getParsedAttributes($element, $data));

		foreach ($cells as $key => $cell)
		{
			$content = StringHelper::parseVariable($cell, $data);

			$attribs = static::getParsedAttributes($cell, $data);

			$grid->setRowCell($key, $content, $attribs);
		}

		reset($element);

		return $cells;
	}
}
