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
use Windwalker\Html\HtmlElements;
use Windwalker\Xul\AbstractXulRenderer;

/**
 * Class TableRenderer
 *
 * @since 1.0
 */
class RowlistRenderer extends AbstractXulRenderer
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
		$itemsKey = XmlHelper::get($element, 'data', 'items');

		$items = ArrayHelper::getByPath($data, $itemsKey);

		$rows = new HtmlElements;

		foreach ($items as $i => $item)
		{
			// Prepare data
			$item = new Data($item);

			$data->xulControl->currentItem = $item;

			// Prepare item for GridHelper
			$data->grid->setItem($item, $i);

			$rows[] = RowRenderer::render('row', $element, $data);
		}

		return $rows;
	}
}
