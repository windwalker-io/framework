<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Form\Field;

use Windwalker\Dom\SimpleXml\XmlHelper;

/**
 * The FieldHelper class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class FieldHelper
{
	/**
	 * createByXml
	 *
	 * @param \SimpleXmlElement $xml
	 *
	 * @return  FieldInterface
	 */
	public static function createByXml(\SimpleXmlElement $xml)
	{
		$classTmpl = 'Windwalker\\Form\\Field\\%sField';

		$type = XmlHelper::get($xml, 'type', 'text');

		$class = sprintf($classTmpl, ucfirst($type));

		if (!class_exists($class))
		{
			// Fallback to TextField
			$class = sprintf($classTmpl, 'Text');
		}

		return new $class($xml);
	}
}
 