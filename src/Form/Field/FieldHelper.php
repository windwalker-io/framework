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
	 * @param \SplPriorityQueue $namespaces
	 *
	 * @return  FieldInterface
	 */
	public static function createByXml(\SimpleXmlElement $xml, \SplPriorityQueue $namespaces)
	{
		$classTmpl = 'Windwalker\\Form\\Field\\Type\\';

		$type = XmlHelper::get($xml, 'type', 'text');

		$class = static::findFieldClass($type, $namespaces);

		if (!$class)
		{
			$class = $classTmpl . ucfirst($type) . 'Field';
		}

		if (!class_exists($class))
		{
			// Fallback to TextField
			$class = $classTmpl . 'TextField';
		}

		return new $class($xml);
	}

	/**
	 * findFieldClass
	 *
	 * @param string            $name
	 * @param \SplPriorityQueue $namespaces
	 *
	 * @return  string|bool
	 */
	protected static function findFieldClass($name, \SplPriorityQueue $namespaces)
	{
		foreach ($namespaces as $namespace)
		{
			$class = trim($namespace, '\\') . '\\' . ucfirst($name) . 'Field';

			if (class_exists($class))
			{
				return $class;
			}
		}

		return false;
	}
}
 