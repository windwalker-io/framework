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
class TabsetRenderer extends AbstractXulRenderer
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
		$tabsetName = XmlHelper::get($element, 'name', 'my');

		$data->tabSetName = $tabsetName;

		$html = \JHtmlBootstrap::startTabSet($tabsetName, array('active' => XmlHelper::get($element, 'active')));

		$html .= $e = static::renderChildren($element, $data);

		$html .= \JHtmlBootstrap::endTabSet();

		return $html;
	}
}