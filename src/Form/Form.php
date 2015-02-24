<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Form;

use Windwalker\Form\Exception\FormValidFailException;
use Windwalker\Form\Field\AbstractField;
use Windwalker\Form\Field\ListField;
use Windwalker\Form\Validate\ValidateResult;

if (!class_exists('CallbackFilterIterator'))
{
	include_once __DIR__ . '/Compat/CallbackFilterIterator.php';
}

/**
 * The Form class.
 * 
 * @since  2.0
 */
class Form implements \IteratorAggregate
{
	/**
	 * Property fields.
	 *
	 * @var  AbstractField[]
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
	 * Property errors.
	 *
	 * @var  ValidateResult[]
	 */
	protected $errors = array();

	/**
	 * Class init.
	 *
	 * @param string $control
	 */
	public function __construct($control = '')
	{
		$this->control = $control;
	}

	/**
	 * load
	 *
	 * @param string|\SimpleXMLElement $xml
	 *
	 * @return  static
	 */
	public function loadXml($xml)
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

		return $this;
	}

	/**
	 * loadFile
	 *
	 * @param string $file
	 *
	 * @return  static
	 */
	public function loadFile($file)
	{
		$this->loadXml(file_get_contents($file));

		return $this;
	}

	/**
	 * addFields
	 *
	 * @param \Traversable|\SimpleXMLElement $fields
	 *
	 * @return  static
	 */
	public function addFields($fields, $fieldset = null, $group = null)
	{
		if ($fields instanceof \SimpleXMLElement)
		{
			$fields = $fields->xpath('//field');
		}

		foreach ($fields as $field)
		{
			$this->addField($field, $fieldset, $group);
		}

		return $this;
	}

	/**
	 * addField
	 *
	 * @param string|AbstractField|\SimpleXMLElement  $field
	 * @param string                                  $fieldset
	 * @param string                                  $group
	 *
	 * @return  AbstractField|ListField
	 */
	public function addField($field, $fieldset = null, $group = null)
	{
		$field = FieldHelper::create($field);

		if ($fieldset)
		{
			$field->setFieldset($fieldset);
		}

		if ($group)
		{
			$field->setGroup($group);
		}

		$group    = $field->getGroup();
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

		return $field;
	}

	/**
	 * define
	 *
	 * @param AbstractField $field
	 * @param callable      $closure
	 * @param string        $fieldset
	 * @param string        $group
	 *
	 * @return  $this
	 */
	public function define(AbstractField $field, \Closure $closure, $fieldset = null, $group = null)
	{
		$field = $closure($field, $fieldset, $group);

		$this->addField($field, $fieldset, $group);

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
	public function addFieldNamespace($ns, $priority = 256)
	{
		FieldHelper::addNamespace($ns, $priority);

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
	public function addFilterNamespace($ns, $priority = 256)
	{
		FilterHelper::addNamespace($ns, $priority);

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
	public function addValidatorNamespace($ns, $priority = 256)
	{
		ValidatorHelper::addNamespace($ns, $priority);

		return $this;
	}

	/**
	 * Retrieve an external iterator
	 *
	 * @return \Iterator|AbstractField[] An instance of an object implementing Iterator or Traversable
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
	 * @return  \CallbackFilterIterator|AbstractField[] An instance of an object implementing Iterator or Traversable
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
	 * @return  AbstractField
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
	 * @return  AbstractField[]
	 */
	public function getFields($fieldset = null, $group = null)
	{
		/**
		 * Filter field callback.
		 *
		 * @param AbstractField          $current
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
	 * setAttributes
	 *
	 * @param string $name
	 * @param mixed  $value
	 * @param string $fieldset
	 * @param string $group
	 *
	 * @return  $this
	 */
	public function setAttributes($name, $value, $fieldset = null, $group = null)
	{
		foreach ($this->getFields($fieldset, $group) as $field)
		{
			$field->setAttribute($name, $value);
		}

		return $this;
	}

	/**
	 * appendAttributes
	 *
	 * @param string $name
	 * @param mixed  $value
	 * @param string $fieldset
	 * @param string $group
	 *
	 * @return  static
	 */
	public function appendAttributes($name, $value, $fieldset = null, $group = null)
	{
		foreach ($this->getFields($fieldset, $group) as $field)
		{
			$field->appendAttribute($name, $value);
		}

		return $this;
	}

	/**
	 * bind
	 *
	 * @param array $data
	 *
	 * @return  $this
	 */
	public function bind($data)
	{
		foreach ($this->fields as $name => $field)
		{
			$value = FormHelper::getByPath($data, $name);

			$field->setValue($value);
		}

		return $this;
	}

	/**
	 * filter
	 *
	 * @return  static
	 */
	public function filter()
	{
		foreach ($this->fields as $field)
		{
			$field->filter();
		}

		return $this;
	}

	/**
	 * validate
	 *
	 * @throws  FormValidFailException
	 * @return  boolean
	 */
	public function validate()
	{
		$errors = array();

		foreach ($this->fields as $field)
		{
			$result = $field->validate();

			if ($result->isFailure())
			{
				$errors[] = $result;
			}
		}

		if ($errors)
		{
			$this->setErrors($errors);

			return false;
		}

		return true;
	}

	/**
	 * getViews
	 *
	 * @param string $fieldset
	 * @param string $group
	 *
	 * @return  array
	 */
	public function getViews($fieldset = null, $group = null)
	{
		$views = array();

		foreach ($this->getFields($fieldset, $group) as $field)
		{
			$views[$field->getName(true)] = array(
				'label' => $field->getLabel(),
				'value' => $field->renderView()
			);
		}

		return $views;
	}

	/**
	 * prepareStore
	 *
	 * @param string $fieldset
	 * @param string $group
	 *
	 * @return  void
	 */
	public function prepareStore($fieldset = null, $group = null)
	{
		foreach ($this->getFields($fieldset, $group) as $field)
		{
			$field->prepareStore();
		}
	}

	/**
	 * renderField
	 *
	 * @param string $name
	 * @param string $group
	 *
	 * @return  string
	 */
	public function renderField($name, $group = '')
	{
		$field = $this->getField($name, $group);

		return $field->render();
	}

	/**
	 * renderFields
	 *
	 * @param string $fieldset
	 * @param string $group
	 *
	 * @return  string
	 */
	public function renderFields($fieldset = null, $group = null)
	{
		$output = '';

		foreach ($this->getFields($fieldset, $group) as $field)
		{
			$output .= "\n" . $field->render();
		}

		return $output;
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

		foreach ($this->fields as $field)
		{
			$field->setControl($control);
		}

		return $this;
	}

	/**
	 * defineFormFields
	 *
	 * @param FieldDefinitionInterface $fields
	 *
	 * @return  $this
	 */
	public function defineFormFields(FieldDefinitionInterface $fields)
	{
		$fields->define($this);

		return $this;
	}

	/**
	 * Method to get property Errors
	 *
	 * @return  ValidateResult[]
	 */
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * Method to set property errors
	 *
	 * @param   ValidateResult[] $errors
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setErrors($errors)
	{
		$this->errors = $errors;

		return $this;
	}

	/**
	 * getValues
	 *
	 * @param string $fieldset
	 * @param string $group
	 *
	 * @return  $this
	 */
	public function getValues($fieldset = null, $group = null)
	{
		$data = array();

		foreach ($this->getFields($fieldset, $group) as $name => $field)
		{
			FormHelper::setByPath($data, $name, $field->getValue());
		}

		return $data;
	}
}
