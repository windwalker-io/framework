<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Controller;

/**
 * The AbstractMultiActionController class.
 * 
 * @since  {DEPLOY_VERSION}
 */
class AbstractMultiActionController extends AbstractController
{
	/**
	 * Property task.
	 *
	 * @var string
	 */
	protected $action = 'index';

	/**
	 * Property actionTmpl.
	 *
	 * @var  string
	 */
	protected $actionTmpl = '%sAction';

	/**
	 * Property arguments.
	 *
	 * @var  array
	 */
	protected $arguments = array();

	/**
	 * Execute the controller.
	 *
	 * @return  mixed Return executed result.
	 */
	public function execute()
	{
		$action = sprintf($this->actionTmpl, $this->action);

		if (!is_callable(array($this, $action)))
		{
			throw new \LogicException(get_called_class() . '::' . $action . '() not exists.');
		}

		return call_user_func_array(array($this, $action), $this->arguments);
	}

	/**
	 * Method to get property Arguments
	 *
	 * @return  array
	 */
	public function getArguments()
	{
		return $this->arguments;
	}

	/**
	 * Method to set property arguments
	 *
	 * @param   array $arguments
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setArguments($arguments)
	{
		$this->arguments = $arguments;

		return $this;
	}

	/**
	 * doExecute
	 *
	 * @return  mixed
	 */
	protected function doExecute()
	{
		throw new \LogicException('Please override this method');
	}

	/**
	 * Method to get property Action
	 *
	 * @return  string
	 */
	public function getAction()
	{
		return $this->action;
	}

	/**
	 * Method to set property action
	 *
	 * @param   string $action
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setAction($action)
	{
		$this->action = $action;

		return $this;
	}
}
