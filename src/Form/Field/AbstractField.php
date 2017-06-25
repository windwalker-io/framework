<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Form\Field;

use Windwalker\Dom\HtmlElement;
use Windwalker\Dom\SimpleXml\XmlHelper;
use Windwalker\Form\Filter\FilterComposite;
use Windwalker\Form\Filter\FilterInterface;
use Windwalker\Form\FilterHelper;
use Windwalker\Form\Form;
use Windwalker\Form\Validate\ValidateResult;
use Windwalker\Form\ValidatorHelper;
use Windwalker\Validator\ValidatorComposite;
use Windwalker\Validator\ValidatorInterface;

/**
 * The AbstractField class.
 *
 * @method $this class($value)
 * @method $this labelClass($value)
 * @method $this controlClass($value)
 * @method $this addClass($value)
 * @method $this removeClass($value)
 * @method $this addLabelClass($value)
 * @method $this removeLabelClass($value)
 * @method $this addControlClass($value)
 * @method $this removeControlClass($value)
 *
 * @since  2.0
 */
abstract class AbstractField
{
	/**
	 * Property type.
	 *
	 * @var  string
	 */
	protected $type = '';

	/**
	 * Property element.
	 *
	 * @var  string
	 */
	protected $element = 'input';

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
	 * Property control.
	 *
	 * @var  string
	 */
	protected $control = null;

	/**
	 * Property label.
	 *
	 * @var string
	 */
	protected $label;

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
	protected $attributes = [];

	/**
	 * Property required.
	 *
	 * @var  boolean
	 */
	protected $required = false;

	/**
	 * Property $validator.
	 *
	 * @var  ValidatorComposite
	 */
	protected $validator = null;

	/**
	 * Property filter.
	 *
	 * @var  FilterComposite
	 */
	protected $filter = null;

	/**
	 * Property attrs.
	 *
	 * @var  array
	 */
	protected $attrs = [];

	/**
	 * The value of false.
	 *
	 * @var  array
	 */
	protected $falseValue = [
		'disabled',
		'false',
		'null',
		'0',
		'no',
		'none'
	];

	/**
	 * The value of true.
	 *
	 * @var  array
	 */
	protected $trueValue = [
		'true',
		'yes',
		'1'
	];

	/**
	 * Property form.
	 *
	 * @var  Form
	 */
	protected $form;

	/**
	 * Constructor.
	 *
	 * @param string $name
	 * @param string $label
	 * @param array  $attributes
	 * @param string $filter
	 * @param string $validator
	 *
	 * @throws \InvalidArgumentException
	 */
	public function __construct($name = null, $label = null, $attributes = [], $filter = null, $validator = null)
	{
		if ($name instanceof \SimpleXMLElement)
		{
			$this->handleXml($name);
		}
		else
		{
			$this->name = $name;
			$this->label = $label;

			$this->attributes = $attributes;
		}

		$this->filter = $filter ? : explode(',', $this->getAttribute('filter'));

		$this->validator = $validator ? : explode(',', $this->getAttribute('validator'));

		$this->required = $this->getBool('required', false);

		// B/C for older version
		if ((is_array($filter) && is_callable($filter)) || !is_array($filter))
		{
			$filter = [$filter];
		}

		$this->resetFilters()->addFilters($filter);

		// B/C for older version
		if ((is_array($validator) && is_callable($validator)) || !is_array($validator))
		{
			$validator = [$validator];
		}

		$this->resetValidators()->addValidators($validator);
	}

	/**
	 * getInput
	 *
	 * @return  string
	 */
	public function renderInput()
	{
		$attrs = $this->prepareAttributes();

		if ($this->form && $this->form->getRenderer())
		{
			return $this->form->getRenderer()->renderInput($this, $attrs);
		}

		return $this->buildInput($attrs);
	}

	/**
	 * buildInput
	 *
	 * @param array $attrs
	 *
	 * @return  mixed
	 */
	public function buildInput($attrs)
	{
		return new HtmlElement($this->element, null, $attrs);
	}

	/**
	 * prepareRenderInput
	 *
	 * @param array $attrs
	 *
	 * @return  array
	 */
	abstract public function prepare(&$attrs);

	/**
	 * prepareAttributes
	 *
	 * @return  array
	 */
	public function prepareAttributes()
	{
		$attrs = [];

		$this->prepare($attrs);

		$attrs = array_merge($attrs, (array) $this->getAttribute('attribs'));

		return $attrs;
	}

	/**
	 * getLabel
	 *
	 * @return  string
	 */
	public function renderLabel()
	{
		$attrs['id']    = $this->getAttribute('labelId', $this->getId() . '-label');
		$attrs['class'] = $this->getAttribute('labelClass');
		$attrs['for']   = $this->getAttribute('for', $this->getId());
		$attrs['title'] = $this->getAttribute('description');

		$attrs = array_merge($attrs, (array) $this->getAttribute('labelAttribs'));

		if ($this->form && $this->form->getRenderer())
		{
			return $this->form->getRenderer()->renderLabel($this, $attrs);
		}

		$label = $this->getLabel();

		if ($this->required)
		{
			$label = '<span class="windwalker-input-required-hint">*</span> ' . $label;
		}

		return (string) new HtmlElement('label', $label, $attrs);
	}

	/**
	 * renderView
	 *
	 * @return  string
	 */
	public function renderView()
	{
		return $this->value;
	}

	/**
	 * render
	 *
	 * @return  string
	 */
	public function render()
	{
		$attrs['id'] = $this->getAttribute('controlId', $this->getId() . '-control');
		$attrs['class'] = $this->type . '-field ' . $this->getAttribute('controlClass');

		$attrs = array_merge($attrs, (array) $this->getAttribute('controlAttribs'));

		if ($this->form && $this->form->getRenderer())
		{
			return $this->form->getRenderer()->renderField($this, $attrs);
		}

		$label = $this->renderLabel();
		$input = $this->renderInput();

		return (string) new HtmlElement('div', $label . $input, $attrs);
	}

	/**
	 * getLabel
	 *
	 * @return  mixed
	 */
	public function getLabel()
	{
		return $this->label;
	}

	/**
	 * getId
	 *
	 * @return  string
	 */
	public function getId()
	{
		$control = $this->control ? $this->control . '/' : '';

		return 'input-' . preg_replace('/[^A-Z0-9_]+/i', '-', $control . $this->getName(true));
	}

	/**
	 * validate
	 *
	 * @return  ValidateResult
	 */
	public function validate()
	{
		$result = new ValidateResult;

		if ($this->required && !$this->checkRequired())
		{
			return $result->setMessage(sprintf('Field %s value not allow empty.', $this->getLabel()))
				->setResult(ValidateResult::STATUS_REQUIRED)
				->setField($this);
		}

		if ($this->value !== null && $this->value !== '' && $this->validator && !$this->checkRule())
		{
			return $result->setMessage(sprintf('Field %s validate fail.', $this->getLabel()))
				->setResult(ValidateResult::STATUS_FAILURE)
				->setField($this);
		}

		return $result;
	}

	/**
	 * checkRequired
	 *
	 * @return  mixed
	 */
	public function checkRequired()
	{
		if (is_array($this->value))
		{
			return (bool) $this->value;
		}

		$value = (string) $this->value;

		if ($this->value || $value === '0')
		{
			return true;
		}

		return false;
	}

	/**
	 * checkRule
	 *
	 * @return  mixed
	 */
	public function checkRule()
	{
		return $this->getValidator()->validate($this->value);
	}

	/**
	 * filter
	 *
	 * @return  static
	 */
	public function filter()
	{
		$filter = $this->getFilter();

		if (is_callable($filter))
		{
			$this->value = call_user_func($filter, $this->value);
		}
		else
		{
			$this->value = $filter->clean($this->value);
		}

		return $this;
	}

	/**
	 * prepareStore
	 *
	 * @return  void
	 */
	public function prepareStore()
	{
	}

	/**
	 * Method to get property Name
	 *
	 * @param bool $withGroup
	 *
	 * @return  string
	 */
	public function getName($withGroup = false)
	{
		$group = $withGroup ? $this->getGroup() : '';

		$group = $group ? $group . '/' : '';

		return $group . $this->name;
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
		$this->fieldName = null;

		$this->name = $name;

		return $this;
	}

	/**
	 * Method to get property FieldName
	 *
	 * @param bool $refresh
	 *
	 * @return  string
	 */
	public function getFieldName($refresh = false)
	{
		if (!$this->fieldName || $refresh)
		{
			// Prevent double '/'
			$names = array_values(array_filter(explode('/', $this->getName(true)), 'strlen'));

			$control = array_values(array_filter(explode('/', $this->getControl()), 'strlen'));

			$names = array_merge($control, $names);

			$control = array_shift($names);

			$names = array_map(
				function ($value)
				{
					return '[' . $value . ']';
				},
				$names
			);

			$this->fieldName = $control . implode('', $names);
		}

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
		$this->fieldName = null;

		$group = str_replace('.', '/', $group);

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
		return ($this->value !== null && $this->value !== '') ? $this->value : $this->getAttribute('default');
	}

	/**
	 * getRawValue
	 *
	 * @return  mixed
	 */
	public function getRawValue()
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
	 * description
	 *
	 * @param   string  $desc
	 *
	 * @return  static
	 */
	public function description($desc)
	{
		$this->setAttribute('description', $desc);

		return $this;
	}

	/**
	 * defaultValue
	 *
	 * @param   string  $value
	 *
	 * @return  static
	 */
	public function defaultValue($value)
	{
		$this->setAttribute('default', $value);

		return $this;
	}

	/**
	 * getDefaultValue
	 *
	 * @return  mixed
	 */
	public function getDefaultValue()
	{
		return $this->getAttribute('default');
	}

	/**
	 * addValidator
	 *
	 * @param ValidatorInterface|callable $validator
	 *
	 * @return  static
	 * @throws \InvalidArgumentException
	 */
	public function addValidator($validator)
	{
		if (!($validator instanceof ValidatorInterface) && !is_callable($validator))
		{
			$validator = ValidatorHelper::create($validator);
		}

		$this->validator->addValidator($validator);

		return $this;
	}

	/**
	 * addValidators
	 *
	 * @param ValidatorInterface[]|callable[] $validators
	 *
	 * @return  static
	 * @throws \InvalidArgumentException
	 */
	public function addValidators(array $validators)
	{
		foreach ($validators as $validator)
		{
			if ($validator === null)
			{
				continue;
			}

			$this->addValidator($validator);
		}

		return $this;
	}

	/**
	 * Method to set property rule
	 *
	 * @param   string|ValidatorInterface $validator
	 *
	 * @return  static  Return self to support chaining.
	 * @throws \InvalidArgumentException
	 *
	 * @deprecated  Use addValidator() instead.
	 */
	public function setValidator($validator)
	{
		$this->addValidator($validator);

		return $this;
	}

	/**
	 * Method to get property Rule
	 *
	 * @return  ValidatorInterface
	 */
	public function getValidator()
	{
		if (!($this->validator instanceof ValidatorInterface))
		{
			$this->validator = ValidatorHelper::create($this->validator);
		}

		return $this->validator;
	}

	/**
	 * resetValidators
	 *
	 * @return  static
	 */
	public function resetValidators()
	{
		$this->validator = new ValidatorComposite;

		return $this;
	}

	/**
	 * addFilter
	 *
	 * @param  FilterInterface|callable $filter
	 *
	 * @return  static
	 * @throws \InvalidArgumentException
	 */
	public function addFilter($filter)
	{
		if (!($filter instanceof FilterInterface) && !is_callable($filter))
		{
			$filter = FilterHelper::create($filter);
		}

		$this->filter->addFilter($filter);

		return $this;
	}

	/**
	 * addFilters
	 *
	 * @param FilterInterface[]|callable[] $filters
	 *
	 * @return  static
	 */
	public function addFilters(array $filters)
	{
		foreach ($filters as $filter)
		{
			if ($filter === null)
			{
				continue;
			}

			$this->addFilter($filter);
		}

		return $this;
	}

	/**
	 * Method to set property filter
	 *
	 * @param   string|FilterInterface|callable $filter
	 *
	 * @return  static  Return self to support chaining.
	 *
	 * @deprecated  Use addFilter() instead.
	 */
	public function setFilter($filter)
	{
		$this->addFilter($filter);

		return $this;
	}

	/**
	 * Method to get property Filter
	 *
	 * @return  string|FilterInterface|callable
	 */
	public function getFilter()
	{
		if (!($this->filter instanceof FilterComposite) && !is_callable($this->filter))
		{
			$this->filter = new FilterComposite([FilterHelper::create($this->filter)]);
		}

		return $this->filter;
	}

	/**
	 * resetFilters
	 *
	 * @return  static
	 */
	public function resetFilters()
	{
		$this->filter = new FilterComposite;

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
		$this->label = XmlHelper::get($xml, 'label');

		$this->attributes = XmlHelper::getAttributes($xml);

		$form = $xml;

		$group = [];

		while ($parent = $form->xpath('..'))
		{
			$parent = $parent[0];

			$name = $parent->getName();

			if ($name === 'fieldset')
			{
				$this->fieldset = $this->fieldset ? : (string) $parent['name'];
			}
			elseif ($name === 'group')
			{
				array_unshift($group, (string) $parent['name']);
			}

			$form = $parent;
		}

		$this->group = implode('/', $group);
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
		$this->fieldName = null;

		$control = str_replace('.', '/', $control);

		$this->control = $control;

		return $this;
	}

	/**
	 * label
	 *
	 * @param string $label
	 *
	 * @return  static
	 */
	public function label($label)
	{
		$this->label = $label;

		return $this;
	}

	/**
	 * required
	 *
	 * @param bool $value
	 *
	 * @return  static
	 */
	public function required($value = true)
	{
		$this->setAttribute('required', $value);

		$this->required = $value;

		return $this;
	}

	/**
	 * disabled
	 *
	 * @param bool $value
	 *
	 * @return  static
	 */
	public function disabled($value = true)
	{
		$this->setAttribute('disabled', $value);

		return $this;
	}

	/**
	 * readonly
	 *
	 * @param bool $value
	 *
	 * @return  static
	 */
	public function readonly($value = true)
	{
		$this->setAttribute('readonly', $value);

		return $this;
	}

	/**
	 * class
	 *
	 * @param   string  $value
	 *
	 * @return  static
	 *
	 * @deprecated  Use class() instead.
	 */
	public function setClass($value)
	{
		$this->setAttribute('class', $value);

		return $this;
	}

	/**
	 * addClassName
	 *
	 * @param string $to
	 * @param mixed  $value
	 *
	 * @return  static
	 *
	 * @TODO  Use Accessors and magic call to handle all attributes.
	 */
	protected function addClassName($to = 'class', $value)
	{
		$classes = explode(' ', (string) $this->getAttribute($to));

		$value = array_merge($classes, is_string($value) ? explode(' ', $value) : $value);

		return $this->set($to, implode(' ', array_unique($value)));
	}

	/**
	 * removeClass
	 *
	 * @param string       $from
	 * @param string|array $value
	 *
	 * @return static
	 *
	 * @TODO  Use Accessors and magic call to handle all attributes.
	 */
	public function removeClassName($from = 'class', $value)
	{
		$classes = explode(' ', (string) $this->getAttribute($from));

		$value = array_diff($classes, is_string($value) ? explode(' ', $value) : $value);

		return $this->set($from, implode(' ', array_unique($value)));
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

	/**
	 * getAttribute
	 *
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @return  static
	 */
	public function setAttribute($name, $value)
	{
		$this->attributes[$name] = $value;

		return $this;
	}

	/**
	 * Get attribute. Alias of `getAttribute()`.
	 *
	 * @param string  $attr    The attribute name.
	 * @param mixed   $default The default value.
	 *
	 * @return mixed The return value of this attribute.
	 */
	public function get($attr, $default = null)
	{
		return $this->getAttribute($attr, $default);
	}

	/**
	 * set
	 *
	 * @param string $attr
	 * @param mixed  $value
	 *
	 * @return  static
	 */
	public function set($attr, $value)
	{
		$this->setAttribute($attr, $value);

		return $this;
	}

	/**
	 * attr
	 *
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @return  static|mixed
	 */
	public function attr($name, $value = null)
	{
		$attrs = (array) $this->getAttribute('attribs');

		if ($value === null)
		{
			return isset($attrs[$name]) ? $attrs[$name] : null;
		}

		$attrs[$name] = $value;

		$this->setAttribute('attribs', $attrs);

		return $this;
	}

	/**
	 * controlAttr
	 *
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @return  static|mixed
	 */
	public function controlAttr($name, $value = null)
	{
		$attrs = (array) $this->getAttribute('controlAttribs');

		if ($value === null)
		{
			return isset($attrs[$name]) ? $attrs[$name] : null;
		}

		$attrs[$name] = $value;

		$this->setAttribute('controlAttribs', $attrs);

		return $this;
	}

	/**
	 * labelAttr
	 *
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @return  static|mixed
	 */
	public function labelAttr($name, $value = null)
	{
		$attrs = (array) $this->getAttribute('labelAttribs');

		if ($value === null)
		{
			return isset($attrs[$name]) ? $attrs[$name] : null;
		}

		$attrs[$name] = $value;

		$this->setAttribute('labelAttribs', $attrs);

		return $this;
	}

	/**
	 * append
	 *
	 * @param string $attr
	 * @param string $value
	 *
	 * @return  static
	 */
	public function appendAttribute($attr, $value)
	{
		$this->setAttribute($attr, trim($this->getAttribute($attr) . $value));

		return $this;
	}

	/**
	 * prependAttribute
	 *
	 * @param string $attr
	 * @param string $value
	 *
	 * @return  static
	 */
	public function prependAttribute($attr, $value)
	{
		$this->setAttribute($attr, trim($value . $this->getAttribute($attr)));

		return $this;
	}

	/**
	 * Method to convert some string like `true`, `1`, `yes` to boolean TRUE,
	 * and `no`, `false`, `disabled`, `null`, `none`, `0` string to boolean FALSE.
	 *
	 * @param string  $attr    The attribute name.
	 * @param mixed   $default The default value.
	 *
	 * @return mixed The return value of this attribute.
	 */
	public function getBool($attr, $default = null)
	{
		$value = $this->getAttribute($attr, $default);

		if (in_array((string) $value, $this->falseValue) || !$value)
		{
			return false;
		}

		return true;
	}

	/**
	 * Just an alias of `getBool()` but FALSE will return TRUE.
	 *
	 * @param string  $attr    The attribute name.
	 * @param mixed   $default The default value.
	 *
	 * @return mixed The return value of this attribute.
	 */
	public function getFalse($attr, $default = null)
	{
		return !$this->getBool($attr, $default);
	}

	/**
	 * Get all attributes.
	 *
	 * @return  array The return values of all attributes.
	 */
	public function getAttributes()
	{
		return $this->attributes;
	}

	/**
	 * If this attribute not exists, use this value as default, or we use original value from xml.
	 *
	 * @param string            $attr    The attribute name.
	 * @param string            $value   The value to set as default.
	 *
	 * @return  void
	 */
	public function def($attr, $value)
	{
		$this->attributes[$attr] = isset($this->attributes[$attr]) ? $this->attributes[$attr] : (string) $value;
	}

	/**
	 * Method to get property Type
	 *
	 * @return  string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Method to set property type
	 *
	 * @param   string $type
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function type($type)
	{
		$this->type = $type;

		return $this;
	}

	/**
	 * Method to get property Form
	 *
	 * @return  Form
	 */
	public function getForm()
	{
		return $this->form;
	}

	/**
	 * Method to set property form
	 *
	 * @param   Form $form
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setForm($form)
	{
		$this->form = $form;

		return $this;
	}

	/**
	 * Escape html string.
	 *
	 * @param   string  $text
	 *
	 * @return  string
	 *
	 * @since  2.1.9
	 */
	public function escape($text)
	{
		return htmlspecialchars($text, ENT_COMPAT, 'UTF-8');
	}

	/**
	 * getAccessors
	 *
	 * @return  array
	 *
	 * @since   3.1.2
	 */
	protected function getAccessors()
	{
		return [
			'class',
			'labelClass',
			'controlClass'
		];
	}

	/**
	 * __call
	 *
	 * @param   string $method
	 * @param   array  $args
	 *
	 * @return  mixed
	 *
	 * @throws \BadMethodCallException
	 */
	public function __call($method, $args)
	{
		$accessors = $this->getAccessors();

		if (isset($accessors[$method]))
		{
			$attribute = $accessors[$method];
		}

		if (in_array($method, $accessors, true))
		{
			$attribute = $method;
		}

		if (isset($attribute))
		{
			if (count($args) > 0)
			{
				$this->setAttribute($attribute, $args[0]);

				return $this;
			}

			return $this->getAttribute($attribute);
		}

		switch($method)
		{
			case 'addClass':
				return $this->addClassName('class', $args[0]);
			case 'removeClass':
				return $this->removeClassName('class', $args[0]);
			case 'addLabelClass':
				return $this->addClassName('labelClass', $args[0]);
			case 'removeLabelClass':
				return $this->removeClassName('labelClass', $args[0]);
			case 'addControlClass':
				return $this->addClassName('controlClass', $args[0]);
			case 'removeControlClass':
				return $this->removeClassName('controlClass', $args[0]);
		}

		throw new \BadMethodCallException(sprintf('Method: %s not exists', $method));
	}
}
