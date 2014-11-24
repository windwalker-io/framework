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
 * The HiddenField class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class HiddenField extends AbstractField
{
	/**
	 * Property type.
	 *
	 * @var  string
	 */
	protected $type = 'hidden';

	/**
	 * prepareRenderInput
	 *
	 * @param array $attrs
	 *
	 * @return  array
	 */
	public function prepare(&$attrs)
	{
		$attrs['type']     = 'hidden';
		$attrs['name']     = $this->getFieldName();
		$attrs['id']       = $this->getAttribute('id', $this->getId());
		$attrs['class']    = $this->getAttribute('class');
		$attrs['disabled'] = $this->getAttribute('disabled');
		$attrs['onchange'] = $this->getAttribute('onchange');
		$attrs['value']    = $this->getValue();
	}
}

