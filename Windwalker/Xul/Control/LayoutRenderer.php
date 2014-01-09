<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Xul\Control;

use Windwalker\Helper\XmlHelper;
use Windwalker\View\Layout\FileLayout;

/**
 * Class CallRenderer
 *
 * @since 1.0
 */
class LayoutRenderer extends CallRenderer
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
		$layoutName = XmlHelper::get($element, 'name');

		if (!$layoutName)
		{
			throw new \InvalidArgumentException('Please add "name" to <layout> tag.');
		}

		$layout = new FileLayout($layoutName);

		$displayData = array('view' => $data);

		$args = static::getArguments($element, $data, 'data');

		$displayData = array_merge($displayData, $args);

		return $layout->render($displayData);
	}
}
