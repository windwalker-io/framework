<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Session\Handler;

/**
 * Class AbstractHandler
 *
 * @since 1.0
 */
abstract class AbstractHandler implements HandlerInterface
{
	/**
	 * Property prefix.
	 *
	 * @var  string
	 */
	protected $prefix = null;

	/**
	 * Class init.
	 *
	 * @param array $options
	 */
	public function __construct($options = array())
	{
		$this->prefix = isset($options['prefix']) ? $options['prefix'] : 'wws_';
	}

	/**
	 * register
	 *
	 * @return  mixed
	 */
	public function register()
	{
		session_set_save_handler($this, true);
	}
}

