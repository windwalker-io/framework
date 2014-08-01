<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Form\Field\Type;

use Windwalker\Html\Select\RadioList;

/**
 * The RadioField class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class RadioField extends ListField
{
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
			$option->setAttribute('id', $this->getAttribute('id', $this->getId()) . '-' . $option->getValue());
			$option->setAttribute('name', $this->getFieldName());
		}

		return new RadioList($this->getFieldName(), $options, $this->attributes, $this->getValue());
	}
}
 