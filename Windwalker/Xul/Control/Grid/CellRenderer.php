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
use Windwalker\Helper\ArrayHelper;
use Windwalker\Helper\StringHelper;
use Windwalker\Helper\XmlHelper;
use Windwalker\Xul\AbstractXulRenderer;

/**
 * Class TableRenderer
 *
 * @since 1.0
 */
class CellRenderer extends AbstractXulRenderer
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
		$field = XmlHelper::get($element, 'field');

		$ele = new \SimpleXMLElement('<root></root>');

		if (!$field)
		{
			$ele[0] = (string) static::renderChildren($element, $data);

			return $ele;
		}

		$ele[0] = $data->xulControl->currentItem->$field;

		return $ele;
	}
}
