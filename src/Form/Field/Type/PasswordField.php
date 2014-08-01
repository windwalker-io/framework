<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Form\Field\Type;

/**
 * The PasswordField class.
 * 
 * @since  {DEPLOY_VERSION}
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
	 * prepareAttributes
	 *
	 * @param array $attrs
	 *
	 * @return  array|void
	 */
	public function prepareAttributes(&$attrs)
	{
		parent::prepareAttributes($attrs);

		$attrs['autocomplete'] = $this->getAttribute('autocomplete');
		$attrs['type'] = 'password';
	}
}

