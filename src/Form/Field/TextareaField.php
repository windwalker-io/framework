<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Form\Field;

use Windwalker\Dom\HtmlElement;

/**
 * The TextareaField class.
 * 
 * @since  2.0
 */
class TextareaField extends TextField
{
	/**
	 * Property type.
	 *
	 * @var  string
	 */
	protected $type = 'textarea';

	/**
	 * Property element.
	 *
	 * @var  string
	 */
	protected $element = 'textarea';

	/**
	 * prepareRenderInput
	 *
	 * @param array $attrs
	 *
	 * @return  array
	 */
	public function prepare(&$attrs)
	{
		$attrs['name']     = $this->getFieldName();
		$attrs['id']       = $this->getAttribute('id', $this->getId());
		$attrs['class']    = $this->getAttribute('class');
		$attrs['readonly'] = $this->getAttribute('readonly');
		$attrs['disabled'] = $this->getAttribute('disabled');
		$attrs['onchange'] = $this->getAttribute('onchange');
		$attrs['required'] = $this->required;

		$attrs['cols'] = $this->getAttribute('cols');
		$attrs['rows'] = $this->getAttribute('rows');

		$attrs = array_merge($attrs, (array) $this->getAttribute('attribs'));
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
		return new HtmlElement($this->element, $this->getValue(), $attrs);
	}
}

