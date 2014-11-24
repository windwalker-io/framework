<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Form\Field;

use Windwalker\Html\Select\CheckboxList;

/**
 * The CheckboxesField class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class CheckboxesField extends ListField
{
	/**
	 * Property type.
	 *
	 * @var  string
	 */
	protected $type = 'checkboxes';

	/**
	 * buildInput
	 *
	 * @param array $attrs
	 *
	 * @return  mixed|void
	 */
	public function buildInput($attrs)
	{
		$options = $this->getOptions();

		foreach ($options as $option)
		{
			// $option->setAttribute('id', $this->getAttribute('id', $this->getId()) . '-' . $option->getValue());
			$option->setAttribute('name', $this->getFieldName());
		}

		return new CheckboxList($this->getFieldName(), $options, $attrs, $this->getValue());
	}

	/**
	 * getValue
	 *
	 * @return  array
	 */
	public function getValue()
	{
		$value = parent::getValue();

		if (is_string($value))
		{
			$value = explode(',', $value);
		}

		return $value;
	}
}
