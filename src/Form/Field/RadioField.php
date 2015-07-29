<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Form\Field;

use Windwalker\Html\Select\RadioList;

/**
 * The RadioField class.
 * 
 * @since  2.0
 */
class RadioField extends ListField
{
	/**
	 * Property type.
	 *
	 * @var  string
	 */
	protected $type = 'radio';

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
			$option->setAttribute('name', $this->getFieldName());
		}

		return new RadioList($this->getFieldName(), $options, $attrs, $this->getValue());
	}
}

