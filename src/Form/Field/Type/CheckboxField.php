<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Form\Field\Type;

use Windwalker\Form\Field\AbstractField;

/**
 * The CheckboxField class.
 * 
 * @since  {DEPLOY_VERSION}
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
	public function prepareAttributes(&$attrs)
	{
		$value = $this->getValue();

		$attrs['type']     = 'checkbox';
		$attrs['name']     = $this->getFieldName();
		$attrs['id']       = $this->getAttribute('id', $this->getId());
		$attrs['class']    = $this->getAttribute('class');
		$attrs['readonly'] = $this->getAttribute('readonly');
		$attrs['disabled'] = $this->getAttribute('disabled');
		$attrs['onchange'] = $this->getAttribute('onchange');
		$attrs['value']    = $this->getAttribute('onchange');
		$attrs['checked']  = $value ? 'true' : null;
	}
}
