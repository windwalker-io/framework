<?php
/**
 * Part of formosa project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Controller;

/**
 * Class AbstractController
 *
 * @since 1.0
 */
abstract class AbstractController implements ControllerInterface
{
	/**
	 * Property input.
	 *
	 * @var  object
	 */
	protected $input = null;

	/**
	 * Property app.
	 *
	 * @var  object
	 */
	protected $app = null;

	/**
	 * Class init.
	 *
	 * @param object $input
	 * @param object $app
	 */
	public function __construct($input = null, $app = null)
	{
		$this->app   = $app;
		$this->input = $input;
	}

	/**
	 * getInput
	 *
	 * @return  object
	 */
	public function getInput()
	{
		return $this->input;
	}

	/**
	 * setInput
	 *
	 * @param   object $input
	 *
	 * @return  AbstractController  Return self to support chaining.
	 */
	public function setInput($input)
	{
		$this->input = $input;

		return $this;
	}

	/**
	 * getApplication
	 *
	 * @return  object
	 */
	public function getApplication()
	{
		return $this->app;
	}

	/**
	 * setApplication
	 *
	 * @param   object $app
	 *
	 * @return  AbstractController  Return self to support chaining.
	 */
	public function setApplication($app)
	{
		$this->app = $app;

		return $this;
	}

	/**
	 * Serialize the controller.
	 *
	 * @return  string  The serialized controller.
	 *
	 * @since   1.0
	 */
	public function serialize()
	{
		return serialize($this->getInput());
	}

	/**
	 * Unserialize the controller.
	 *
	 * @param   string  $input  The serialized controller.
	 *
	 * @return  AbstractController  Returns itself to support chaining.
	 */
	public function unserialize($input)
	{
		$input = unserialize($input);

		$this->setInput($input);

		return $this;
	}
}
 