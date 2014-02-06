<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Controller\Resolver;

/**
 * Class ControllerDelegator
 *
 * @since 1.0
 */
class ControllerDelegator
{
	/**
	 * Property class.
	 *
	 * @var  string
	 */
	public $class;

	/**
	 * Property input.
	 *
	 * @var  \JInput
	 */
	public $input;

	/**
	 * Property app.
	 *
	 * @var  \JApplicationBase
	 */
	public $app;

	/**
	 * Property config.
	 *
	 * @var  array
	 */
	public $config;

	/**
	 * getController
	 *
	 * @param string            $class
	 * @param \JInput           $input
	 * @param \JApplicationBase $app
	 * @param array             $config
	 *
	 * @return \Windwalker\Controller\Controller
	 */
	public function getController($class, \JInput $input, \JApplicationBase $app, $config = array())
	{
		$this->class  = $class;
		$this->input  = $input;
		$this->app    = $app;
		$this->config = $config;

		return $this->createController($class);
	}

	/**
	 * createController
	 *
	 * @param   string $class
	 *
	 * @return  \Windwalker\Controller\Controller
	 */
	protected function createController($class)
	{
		return new $class($this->input, $this->app, $this->config);
	}
}
