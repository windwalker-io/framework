<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Form\Field;

/**
 * The PasswordField class.
 * 
 * @since  2.0
 */
class PasswordField extends TextField
{
	/**
	 * Property type.
	 *
	 * @var  string
	 */
	protected $type = 'password';

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

		$attrs['autocomplete'] = $this->getAttribute('autocomplete');
	}
}

