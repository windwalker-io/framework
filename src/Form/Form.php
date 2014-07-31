<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Form;

use Windwalker\Form\Field\FieldHelper;
use Windwalker\Form\Field\FieldInterface;

/**
 * The Form class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class Form implements \IteratorAggregate
{
	/**
	 * Property fields.
	 *
	 * @var  FieldInterface[]
	 */
	protected $fields = array();

	/**
	 * Property control.
	 *
	 * @var  string
	 */
	protected $control = null;

	/**
	 * Property xml.
	 *
	 * @var  \SimpleXmlElement
	 */
	protected $xml = null;

	/**
	 * Property fieldsets.
	 *
	 * @var  string[]
	 */
	protected $fieldsets = array();

	/**
	 * Property groups.
	 *
	 * @var  string[]
	 */
	protected $groups = array();

	/**
	 * Property fieldPaths.
	 *
	 * @var \SplPriorityQueue
	 */
	protected $fieldNamespaces;

	public function __construct($control = '')
	{
		$this->fieldNamespaces = new \SplPriorityQueue;

		$this->control = $control;
	}

	/**
	 * load
	 *
	 * @param string|\SimpleXMLElement $xml
	 *
	 * @return  void
	 */
	public function load($xml)
	{
		if (is_string($xml))
		{
			$this->xml = $xml = simplexml_load_string($xml);
		}
		elseif ($xml instanceof \SimpleXMLElement)
		{
			$this->xml = $xml;
		}

		$this->addFields($xml);
	}

	public function loadFile($file)
	{
		$this->load(file_get_contents($file));
	}

	/**
	 * addFields
	 *
	 * @param \Traversable|\SimpleXMLElement $fields
	 *
	 * @return  $this
	 */
	public function addFields(\Traversable $fields)
	{
		if ($fields instanceof \SimpleXMLElement)
		{
			$fields = $fields->xpath('//field');
		}

		foreach ($fields as $field)
		{
			$this->addField($field);
		}

		return $this;
	}

	/**
	 * addField
	 *
	 * @param string|FieldInterface|\SimpleXMLElement $field
	 *
	 * @return  $this
	 *
	 * @throws \InvalidArgumentException
	 */
	public function addField($field)
	{
		if ($field instanceof \SimpleXMLElement)
		{
			$field = FieldHelper::createByXml($field, $this->fieldNamespaces);
		}
		elseif (is_string($field))
		{
			$xml = new \SimpleXMLElement($field);

			$field = FieldHelper::createByXml($xml, $this->fieldNamespaces);
		}
		elseif (!($field instanceof FieldInterface))
		{
			throw new \InvalidArgumentException(__CLASS__ . '::addField() need FieldInterface or SimpleXMLElement.');
		}

		$group = $field->getGroup();
		$fieldset = $field->getFieldset();

		if ($group && !in_array($group, $this->groups))
		{
			$this->groups[] = $group;
		}

		if ($fieldset && !in_array($fieldset, $this->fieldsets))
		{
			$this->fieldsets[] = $fieldset;
		}

		$field->setControl($this->control);

		$this->fields[$field->getName(true)] = $field;

		return $this;
	}

	/**
	 * Method to set property fieldNamespaces
	 *
	 * @param string $ns
	 * @param int    $priority
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function addFieldNamespace($ns, $priority = 100)
	{
		$this->fieldNamespaces->insert($ns, $priority);

		return $this;
	}

	/**
	 * Retrieve an external iterator
	 *
	 * @return \Traversable|FieldInterface[] An instance of an object implementing Iterator or Traversable
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->fields);
	}

	/**
	 * getCallbackIterator
	 *
	 * @param \Closure $handler
	 *
	 * @return  \CallbackFilterIterator|FieldInterface[] An instance of an object implementing Iterator or Traversable
	 */
	public function getCallbackIterator(\Closure $handler)
	{
		return new \CallbackFilterIterator($this->getIterator(), $handler);
	}

	/**
	 * getField
	 *
	 * @param string $name
	 * @param string $group
	 *
	 * @return  FieldInterface
	 */
	public function getField($name, $group = '')
	{
		if ($group)
		{
			$name = $group . '.' . $name;
		}

		return isset($this->fields[$name]) ? $this->fields[$name] : null;
	}

	/**
	 * removeField
	 *
	 * @param string $name
	 * @param string $group
	 *
	 * @return  $this
	 */
	public function removeField($name, $group = null)
	{
		if ($group)
		{
			$name = $group . '.' . $name;
		}

		if (isset($this->fields[$name]))
		{
			unset($this->fields[$name]);
		}

		return $this;
	}

	/**
	 * removeField
	 *
	 * @param string $fieldset
	 * @param string $group
	 *
	 * @return  $this
	 */
	public function removeFields($fieldset = null, $group = null)
	{
		foreach ($this->fields as $current)
		{
			if ($fieldset && $current->getFieldset() != $fieldset)
			{
				$this->removeField($current->getName(true));
			}

			if ($group && $current->getGroup() != $group)
			{
				$this->removeField($current->getName(true));
			}
		}

		return $this;
	}

	/**
	 * getFields
	 *
	 * @param string $fieldset
	 * @param string $group
	 *
	 * @return  array
	 */
	public function getFields($fieldset = null, $group = null)
	{
		/**
		 * Filter field callback.
		 *
		 * @param FieldInterface          $current
		 * @param string                  $key
		 * @param \CallbackFilterIterator $iterator
		 *
		 * @return  boolean
		 */
		$handler = function($current, $key, $iterator) use ($fieldset, $group)
		{
			if ($fieldset && $current->getFieldset() != $fieldset)
			{
				return false;
			}

			if ($group && $current->getGroup() != $group)
			{
				return false;
			}

			return true;
		};

		return iterator_to_array($this->getCallbackIterator($handler));
	}

	/**
	 * Method to get property Fieldsets
	 *
	 * @return  \string[]
	 */
	public function getFieldsets()
	{
		return $this->fieldsets;
	}

	/**
	 * Method to get property Groups
	 *
	 * @return  \string[]
	 */
	public function getGroups()
	{
		return $this->groups;
	}

	/**
	 * setAttribute
	 *
	 * @param string $field
	 * @param string $name
	 * @param mixed  $value
	 * @param string $group
	 *
	 * @return  $this
	 */
	public function setAttribute($field, $name, $value, $group = null)
	{
		$field = $this->getField($field, $group);

		if ($field)
		{
			$field->setAttribute($name, $value);
		}

		return $this;
	}

	/**
	 * getAttribute
	 *
	 * @param string $field
	 * @param string $name
	 * @param mixed  $default
	 * @param string $group
	 *
	 * @return  mixed|null
	 */
	public function getAttribute($field, $name, $default = null, $group = null)
	{
		$field = $this->getField($field, $group);

		if ($field)
		{
			return $field->getAttribute($name, $default);
		}

		return $default;
	}

	/**
	 * Method to get property Control
	 *
	 * @return  string
	 */
	public function getControl()
	{
		return $this->control;
	}

	/**
	 * Method to set property control
	 *
	 * @param   string $control
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setControl($control)
	{
		$this->control = $control;

		return $this;
	}
}
 