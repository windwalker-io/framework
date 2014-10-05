<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Form;

use Windwalker\Dom\SimpleXml\XmlHelper;
use Windwalker\Form\Field\AbstractField;

/**
 * The FieldHelper class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class FieldHelper extends AbstractFormElementHelper
{
	/**
	 * Property fieldNamespaces.
	 *
	 * @var  \SplPriorityQueue
	 */
	protected static $namespaces = null;

	/**
	 * Property defaultNamespace.
	 *
	 * @var string
	 */
	protected static $defaultNamespace = 'Windwalker\\Form\\Field';

	/**
	 * createField
	 *
	 * @param string|AbstractField|\SimpleXMLElement $field
	 * @param \SplPriorityQueue                      $namespaces
	 *
	 * @throws \InvalidArgumentException
	 *
	 * @return  AbstractField
	 */
	public static function create($field, \SplPriorityQueue $namespaces = null)
	{
		if ($field instanceof \SimpleXMLElement)
		{
			$field = static::createByXml($field, $namespaces);
		}
		elseif (is_string($field))
		{
			$xml = new \SimpleXMLElement($field);

			$field = static::createByXml($xml, $namespaces);
		}
		elseif (!($field instanceof AbstractField))
		{
			throw new \InvalidArgumentException('Windwalker\\Form\\Form::addField() need AbstractField or SimpleXMLElement.');
		}

		return $field;
	}

	/**
	 * createByXml
	 *
	 * @param \SimpleXmlElement $xml
	 * @param \SplPriorityQueue $namespaces
	 *
	 * @return  AbstractField
	 */
	public static function createByXml(\SimpleXmlElement $xml, \SplPriorityQueue $namespaces = null)
	{
		$type = XmlHelper::get($xml, 'type', 'text');

		if (class_exists($type))
		{
			$class = $type;
		}
		else
		{
			$class = static::findFieldClass($type, $namespaces);
		}

		if (!class_exists($class))
		{
			// Fallback to TextField
			$class = static::$defaultNamespace . '\\TextField';
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
	protected static function findFieldClass($name, \SplPriorityQueue $namespaces = null)
	{
		$namespaces = $namespaces ? : static::getNamespaces();

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
