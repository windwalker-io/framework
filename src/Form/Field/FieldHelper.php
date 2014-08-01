<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Form\Field;

use Windwalker\Dom\SimpleXml\XmlHelper;
use Windwalker\Form\Filter\InputFiler;
use Windwalker\Validator\Rule\NoneValidator;
use Windwalker\Validator\Rule\RegexValidator;
use Windwalker\Validator\ValidatorInterface;

/**
 * The FieldHelper class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class FieldHelper
{
	/**
	 * createField
	 *
	 * @param string|FieldInterface|\SimpleXMLElement $field
	 * @param                                         $namespaces
	 *
	 * @throws \InvalidArgumentException
	 * @return  FieldInterface
	 *
	 */
	public static function createField($field, \SplPriorityQueue $namespaces)
	{
		if ($field instanceof \SimpleXMLElement)
		{
			$field = FieldHelper::createByXml($field, $namespaces);
		}
		elseif (is_string($field))
		{
			$xml = new \SimpleXMLElement($field);

			$field = FieldHelper::createByXml($xml, $namespaces);
		}
		elseif (!($field instanceof FieldInterface))
		{
			throw new \InvalidArgumentException(__CLASS__ . '::addField() need FieldInterface or SimpleXMLElement.');
		}

		return $field;
	}

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

		if (class_exists($type))
		{
			$class = $type;
		}
		else
		{
			$class = static::findFieldClass($type, $namespaces);
		}

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

	/**
	 * createFilter
	 *
	 * @param string $filter
	 *
	 * @return  bool|InputFiler
	 */
	public static function createFilter($filter)
	{
		if (class_exists($filter))
		{
			  return new $filter;
		}

		$class = 'Windwalker\\Form\\Filter\\' . ucfirst($filter) . ' Filter';

		if (class_exists($class))
		{
			return new $class;
		}

		return new InputFiler($filter);
	}

	/**
	 * createRule
	 *
	 * @param string $rule
	 *
	 * @throws \InvalidArgumentException
	 * @return  ValidatorInterface
	 */
	public static function createValidator($rule)
	{
		if (!$rule)
		{
			return new NoneValidator;
		}

		if (class_exists($rule))
		{
			return new $rule;
		}

		$class = 'Windwalker\\Validator\\Rule\\' . ucfirst($rule) . ' Validator';

		if (class_exists($class))
		{
			return new $class;
		}

		if (is_string($rule))
		{
			return new RegexValidator($rule);
		}

		throw new \InvalidArgumentException(sprintf('Validator %s is not exists.', $rule));
	}
}

