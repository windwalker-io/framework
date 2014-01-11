<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Xul\Control\Grid;

use JGrid;
use Windwalker\Data\Data;
use Windwalker\Helper\XmlHelper;
use Windwalker\Xul\AbstractXulRenderer;
use Windwalker\Xul\XulEngine;

/**
 * Class TableRenderer
 *
 * @since 1.0
 */
class HeadRenderer extends AbstractXulRenderer
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
		$cols = static::renderChildren($engine, $element, $data);

		$gridHelper = $data->grid;
		$grid       = $data->xulControl->grid;
		$grid->addRow(array(), 1);

		foreach ($cols as $key => $value)
		{
			$attribs = XmlHelper::getAttributes($element);

			$title = XmlHelper::get($value, 'title');

			unset($attribs['title']);

			$grid->addColumn($key, $attribs);

			$grid->setRowCell($key, $title, $attribs);
		}
	}
}
