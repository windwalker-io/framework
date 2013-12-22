<?php
/**
 * Part of Windwalker RAD framework package.
 *
 * @author     Simon Asika <asika32764@gmail.com>
 * @copyright  Copyright (C) 2014 Asikart. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Windwalker\Component;

use Windwalker\Controller\ControllerHelper;
use Windwalker\DI\Container;

/**
 * Class Component
 *
 * @since 2.0
 */
class Component
{
	/**
	 * Property application.
	 *
	 * @var \JApplicationCms
	 */
	protected $application;

	/**
	 * Property container.
	 *
	 * @var \Joomla\DI\Container
	 */
	protected $container;

	/**
	 * Property input.
	 *
	 * @var \JInput
	 */
	protected $input;

	/**
	 * Property name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Property reflection.
	 *
	 * @var \ReflectionClass
	 */
	protected $reflection;

	/**
	 * Property defaultController.
	 *
	 * @var string
	 */
	protected $defaultController;

	/**
	 * Constructor.
	 *
	 * @param string           $name
	 * @param \JInput          $input
	 * @param \JApplicationCms $application
	 * @param Container        $container
	 *
	 * @throws \Exception
	 */
	public function __construct($name = null, $input = null, $application = null, $container = null)
	{
		$this->application = $application ?: \JFactory::getApplication();
		$this->input       = $input       ?: $this->application->input;
		$this->name        = $name;

		$this->prepare();

		if (!$this->name)
		{
			$reflection = $this->getReflection();

			$this->name = $reflection->getShortName();

			$this->name = str_replace('Component', '', $this->name);

			if (!$this->name)
			{
				throw new \Exception('Component need name.');
			}
		}

		$this->container = $container   ?: Container::getInstance($this->name);
	}

	/**
	 * execute
	 *
	 * @return mixed
	 */
	public function execute()
	{
		$this->loadConfiguration();

		$this->init();

		return $this->doExecute();
	}

	/**
	 * doExecute
	 *
	 * @return mixed
	 */
	protected function doExecute()
	{
		$controller = ControllerHelper::getController($this->name, $this->input, $this->application);

		$controller->setComponentPath(JPATH_BASE . '/components/com_' . strtolower($this->name));

		// echo get_class($controller);

		return $controller->setOption('com_' . strtolower($this->name))
			->execute();
	}

	/**
	 * init
	 *
	 * @return void
	 */
	protected function init()
	{
		$task       = $this->input->getWord('task');
		$controller = $this->input->getWord('controller');

		if (!$task && !$controller)
		{
			$this->input->set('task',       $this->defaultController);
			$this->input->set('controller', $this->defaultController);
		}
	}

	/**
	 * prepare
	 *
	 * @return void
	 */
	protected function prepare()
	{
	}

	/**
	 * getContainer
	 *
	 * @return Container
	 */
	public function getContainer()
	{
		return $this->container;
	}

	/**
	 * setContainer
	 *
	 * @param Container $container
	 *
	 * @return Component
	 */
	public function setContainer(Container $container)
	{
		$this->container = $container;

		return $this;
	}

	/**
	 * getApplication
	 *
	 * @return \JApplicationCms
	 */
	public function getApplication()
	{
		return $this->application;
	}

	/**
	 * setApplication
	 *
	 * @param \JApplicationBase $application
	 *
	 * @return $this
	 */
	public function setApplication(\JApplicationBase $application)
	{
		$this->application = $application;

		return $this;
	}

	/**
	 * getInput
	 *
	 * @return \JInput
	 */
	public function getInput()
	{
		return $this->input;
	}

	/**
	 * setInput
	 *
	 * @param \JInput $input
	 *
	 * @return $this
	 */
	public function setInput(\JInput $input)
	{
		$this->input = $input;

		return $this;
	}

	/**
	 * loadConfiguration
	 *
	 * @return void
	 */
	protected function loadConfiguration()
	{
	}

	/**
	 * getReflection
	 *
	 * @return \ReflectionClass
	 */
	public function getReflection()
	{
		if ($this->reflection)
		{
			return $this->reflection;
		}

		$this->reflection = new \ReflectionClass($this);

		return $this->reflection;
	}

	/**
	 * getDefaultController
	 *
	 * @return string
	 */
	public function getDefaultController()
	{
		return $this->defaultController;
	}

	/**
	 * setDefaultController
	 *
	 * @param string $defaultController
	 *
	 * @return $this
	 */
	public function setDefaultController($defaultController)
	{
		$this->defaultController = $defaultController;

		return $this;
	}
}
