<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Xul\Control\Grid;

use Windwalker\Helper\XmlHelper;
use Windwalker\Xul\AbstractXulRenderer;
use Windwalker\Xul\XulEngine;

/**
 * Class SortRenderer
 *
 * @since 1.0
 */
class SortRenderer extends AbstractXulRenderer
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
		$field = XmlHelper::get($element, 'field');

		if (!$field)
		{
			throw new \InvalidArgumentException('"sort" tag need field');
		}

		return $data->grid->sortTitle(static::renderChildren($engine, $element, $data), $field);
	}
}
