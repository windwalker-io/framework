<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Xul\Control;

use JText;
use Windwalker\Helper\ArrayHelper;
use Windwalker\Helper\XmlHelper;
use Windwalker\Html\HtmlElement;
use Windwalker\Html\HtmlElements;
use Windwalker\Xul\XulEngine;

/**
 * Class ColumnRenderer
 *
 * @since 1.0
 */
class SidebarRenderer extends ColumnRenderer
{
	/**
	 * doRender
	 *
	 * @param string            $name
	 * @param \SimpleXmlElement $element
	 * @param mixed             $data
	 *
	 * @throws \LogicException
	 * @return  mixed
	 */
	protected static function doRender($name, XulEngine $engine, \SimpleXmlElement $element, $data)
	{
		$dataKey = XmlHelper::get($element, 'data');

		$sidebar = $dataKey ? ArrayHelper::getByPath($data, $dataKey) : $data->sidebar;

		if (empty($sidebar))
		{
			return '';
		}

		$html = new HtmlElements;

		$html[] = new HtmlElement('h4', JText::_(XmlHelper::get($element, 'title', 'JOPTION_MENUS')));
		$html[] = $sidebar;

		$element->addChild('block', $html);

		if (!isset($data->view->colSpan))
		{
			throw new \LogicException('Please put "sidebar" tag in "row" tag.');
		}

		return parent::doRender($name, $engine, $element, $data);
	}
}
