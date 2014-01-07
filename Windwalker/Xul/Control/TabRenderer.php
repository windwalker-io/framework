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

/**
 * Class TabsetHandler
 *
 * @since 1.0
 */
class TabRenderer extends AbstractXulRenderer
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
		$tabName = XmlHelper::get($element, 'name');

		$label = $element['label'] ? (string) $element['label'] : $data->view->option . '_EDIT_FIELDS_' . $tabName;

		$html = \JHtmlBootstrap::addTab($data->tabSetName, $tabName, \JText::_($label));

		$html .= static::renderChildren($element, $data);

		$html .= \JHtmlBootstrap::endTab();

		return $html;
	}
}
