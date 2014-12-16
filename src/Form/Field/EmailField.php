<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

namespace Windwalker\Form\Field;

/**
 * The EmailField class.
 * 
 * @since  2.0
 */
class EmailField extends TextField
{
	/**
	 * Property type.
	 *
	 * @var  string
	 */
	protected $type = 'email';

	/**
	 * prepare
	 *
	 * @param array $attrs
	 *
	 * @return  array|void
	 */
	public function prepare(&$attrs)
	{
		parent::prepare($attrs);

		$attrs['class'] = 'validate-email ' . $attrs['class'];
	}
}

