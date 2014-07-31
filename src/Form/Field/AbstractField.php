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
 * The AbstractField class.
 * 
 * @since  {DEPLOY_VERSION}
 */
abstract class AbstractField implements FieldInterface
{
	/**
	 * Property name.
	 *
	 * @var  string
	 */
	protected $name = null;

	/**
	 * Property fieldName.
	 *
	 * @var  string
	 */
	protected $fieldName = null;

	/**
	 * Property group.
	 *
	 * @var  string
	 */
	protected $group = null;

	/**
	 * Property fieldset.
	 *
	 * @var  string
	 */
	protected $fieldset = null;

	/**
	 * Property value.
	 *
	 * @var  mixed
	 */
	protected $value = null;

	/**
	 * Property attributes.
	 *
	 * @var  string[]
	 */
	protected $attributes = array();

	/**
	 * Constructor.
	 *
	 * @param string $name
	 * @param array  $attributes
	 */
	public function __construct($name, $attributes = array())
	{
		if ($name instanceof \SimpleXMLElement)
		{
			$this->handleXml($name);
		}
		else
		{
			$this->name = $name;

			$this->attributes = $attributes;
		}
	}

	/**
	 * Method to get property Name
	 *
	 * @return  null
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Method to set property name
	 *
	 * @param   null $name
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setName($name)
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * Method to get property FieldName
	 *
	 * @return  null
	 */
	public function getFieldName()
	{
		return $this->fieldName;
	}

	/**
	 * Method to set property fieldName
	 *
	 * @param   null $fieldName
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setFieldName($fieldName)
	{
		$this->fieldName = $fieldName;

		return $this;
	}

	/**
	 * Method to get property Group
	 *
	 * @return  null
	 */
	public function getGroup()
	{
		return $this->group;
	}

	/**
	 * Method to set property group
	 *
	 * @param   null $group
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setGroup($group)
	{
		$this->group = $group;

		return $this;
	}

	/**
	 * Method to get property Fieldset
	 *
	 * @return  null
	 */
	public function getFieldset()
	{
		return $this->fieldset;
	}

	/**
	 * Method to set property fieldset
	 *
	 * @param   null $fieldset
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setFieldset($fieldset)
	{
		$this->fieldset = $fieldset;

		return $this;
	}

	/**
	 * Method to get property Value
	 *
	 * @return  null
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Method to set property value
	 *
	 * @param   null $value
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setValue($value)
	{
		$this->value = $value;

		return $this;
	}

	/**
	 * handleXml
	 *
	 * @param \SimpleXMLElement $xml
	 *
	 * @return  void
	 */
	protected function handleXml(\SimpleXMLElement $xml)
	{
		$this->name = XmlHelper::get($xml, 'name');
		$this->attributes = XmlHelper::getAttributes($xml);

		$form = $xml;

		$group = array();

		while ($parent = $form->xpath('..'))
		{
			$parent = $parent[0];

			$name = $parent->getName();

			if ($name == 'fieldset')
			{
				$this->fieldset = (string) $parent['name'];
			}
			elseif ($name == 'group')
			{
				array_unshift($group, (string) $parent['name']);
			}

			$form = $parent;
		}

		$this->group = implode('.', $group);
	}

	/**
	 * getAttribute
	 *
	 * @param string $name
	 * @param mixed  $default
	 *
	 * @return  mixed
	 */
	public function getAttribute($name, $default = null)
	{
		return isset($this->attributes[$name]) ? $this->attributes[$name] : $default;
	}
}
