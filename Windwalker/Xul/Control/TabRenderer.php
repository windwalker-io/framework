<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Xul\Control;

use JHtmlBootstrap;
use Windwalker\Helper\XmlHelper;
use Windwalker\Xul\AbstractXulRenderer;
use Windwalker\Xul\XulEngine;

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
	 * @param XulEngine         $engine
	 * @param \SimpleXmlElement $element
	 * @param mixed             $data
	 *
	 * @throws \LogicException
	 * @return  mixed
	 */
	protected static function doRender($name, XulEngine $engine, \SimpleXmlElement $element, $data)
	{
		$tabName = XmlHelper::get($element, 'name');

		$label = $element['label'] ? (string) $element['label'] : $data->view->option . '_EDIT_FIELDS_' . $tabName;

		$html = JHtmlBootstrap::addTab($data->tabSetName, $tabName, \JText::_($label));

		$html .= static::renderChildren($engine, $element, $data);

		$html .= JHtmlBootstrap::endTab();

		return $html;
	}
}
