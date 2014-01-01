<?php
/**
 * Part of joomla321 project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Helper;

/**
 * Class HelperInvoker
 *
 * @since 1.0
 */
class HelperInvoker
{
	/**
	 * Property class.
	 *
	 * @var string
	 */
	protected $class = '';

	/**
	 * Constructor.
	 *
	 * @param string $name
	 */
	public function __construct($name)
	{
		$this->class = '\\Windwalker\\Helper\\' . $name . 'Helper';
	}

	/**
	 * __call
	 *
	 * @param string $name
	 * @param array  $args
	 *
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		return call_user_func_array(array($this->class, $name), $args);
	}
}
