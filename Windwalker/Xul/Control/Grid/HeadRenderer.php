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
	 * @param \SimpleXmlElement $element
	 * @param mixed             $data
	 *
	 * @return  mixed
	 */
	protected static function doRender($name, \SimpleXmlElement $element, $data)
	{
		$cols = static::renderChildren($element, $data);

		$gridHelper = $data->grid;
		$grid       = $data->xulControl->grid;
		$grid->addRow(array(), 1);

		foreach ($cols as $key => $value)
		{
			$title = XmlHelper::get($value, 'title');
			$field = XmlHelper::get($value, 'field');

			$attribs = static::getParsedAttributes($element, $data);

			unset($attribs['title']);
			unset($attribs['field']);

			$grid->addColumn($key, $attribs);

			$grid->setRowCell($key, $gridHelper->sortTitle($title, $field), array());
		}
	}
}
