<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Form\Field;

use Windwalker\Form\Field\AbstractField;

/**
 * The CheckboxField class.
 * 
 * @since  2.0
 */
class CheckboxField extends AbstractField
{
	/**
	 * Property type.
	 *
	 * @var  string
	 */
	protected $type = 'checkbox';

	/**
	 * prepareRenderInput
	 *
	 * @param array $attrs
	 *
	 * @return  array
	 */
	public function prepare(&$attrs)
	{
		$value = $this->getValue();

		$attrs['type']     = 'checkbox';
		$attrs['name']     = $this->getFieldName();
		$attrs['id']       = $this->getAttribute('id', $this->getId());
		$attrs['class']    = $this->getAttribute('class');
		$attrs['readonly'] = $this->getAttribute('readonly');
		$attrs['disabled'] = $this->getAttribute('disabled');
		$attrs['onchange'] = $this->getAttribute('onchange');
		$attrs['value']    = $this->getAttribute('value');
		$attrs['checked']  = $value ? 'true' : null;
	}
}
