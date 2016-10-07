<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Form\Field;

/**
 * The TextField class.
 * 
 * @since  2.0
 */
class TextField extends AbstractField
{
	/**
	 * Property type.
	 *
	 * @var  string
	 */
	protected $type = 'text';

	/**
	 * prepareRenderInput
	 *
	 * @param array $attrs
	 *
	 * @return  array
	 */
	public function prepare(&$attrs)
	{
		$attrs['type']     = $this->type ? : 'text';
		$attrs['name']     = $this->getFieldName();
		$attrs['id']       = $this->getAttribute('id', $this->getId());
		$attrs['class']    = $this->getAttribute('class');
		$attrs['placeholder'] = $this->getAttribute('placeholder');
		$attrs['size']     = $this->getAttribute('size');
		$attrs['maxlength'] = $this->getAttribute('maxlength');
		$attrs['readonly'] = $this->getAttribute('readonly');
		$attrs['disabled'] = $this->getAttribute('disabled');
		$attrs['autofocus'] = $this->getAttribute('autofocus');
		$attrs['autocomplete'] = $this->getAttribute('autocomplete');
		$attrs['form'] = $this->getAttribute('form');
		$attrs['formenctype'] = $this->getAttribute('formenctype');
		$attrs['formmethod'] = $this->getAttribute('formmethod');
		$attrs['formnovalidate'] = $this->getAttribute('formnovalidate');
		$attrs['formtarget'] = $this->getAttribute('formtarget');
		$attrs['list'] = $this->getAttribute('list');
		$attrs['onchange'] = $this->getAttribute('onchange');
		$attrs['onfocus'] = $this->getAttribute('onfocus');
		$attrs['onchange'] = $this->getAttribute('onchange');
		$attrs['value']    = $this->escape($this->getValue());

		$attrs['required'] = $this->required;

		$attrs = array_merge($attrs, (array) $this->getAttribute('attribs'));
	}
}
