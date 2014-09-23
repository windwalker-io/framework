<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Session\Handler;

/**
 * Class AbstractHandler
 *
 * @since {DEPLOY_VERSION}
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
		if (version_compare(phpversion(), '5.4.0', '>='))
		{
			session_set_save_handler($this, true);
		}
		else
		{
			session_set_save_handler(
				array($this, 'open'),
				array($this, 'close'),
				array($this, 'read'),
				array($this, 'write'),
				array($this, 'destroy'),
				array($this, 'gc')
			);
		}
	}
}

