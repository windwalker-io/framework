<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Form\Field;

/**
 * The EmailField class.
 * 
 * @since  {DEPLOY_VERSION}
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

