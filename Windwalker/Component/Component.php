<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Component;

use Windwalker\Controller\Controller;
use Windwalker\DI\Container;
use Windwalker\Helper\LanguageHelper;
use Windwalker\Object\Object;

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
	 * @var  string  Property option.
	 */
	protected $option;

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
	 * Property path.
	 *
	 * @var array
	 */
	protected $path = array(
		'self',
		'site',
		'administrator'
	);

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

		// Guess component name.
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

		$this->option = 'com_' . strtolower($this->name);

		$this->container = $container ?: Container::getInstance($this->option);

		$this->init();
	}

	/**
	 * execute
	 *
	 * @return mixed
	 */
	public function execute()
	{
		$this->loadConfiguration();

		$this->prepare();

		$result = $this->doExecute();

		return $this->postExecute($result);
	}

	/**
	 * doExecute
	 *
	 * @return mixed
	 */
	protected function doExecute()
	{
		/** @var $controller Controller */
		$resolver   = $this->container->get('controller.resolver');
		$controller = $resolver->getController($this->name, $this->input, $this->application);

		$controller->setComponentPath(JPATH_BASE . '/components/' . $this->option);

		return $controller->setContainer($this->container)
			->execute();
	}

	/**
	 * postExecute
	 *
	 * @param mixed $result
	 *
	 * @return  mixed
	 */
	protected function postExecute($result)
	{
		return $result;
	}

	/**
	 * init
	 *
	 * @return void
	 */
	protected function prepare()
	{
	}

	/**
	 * prepare
	 *
	 * @return void
	 */
	protected function init()
	{
		$this->path['self']          = JPATH_BASE . '/components/' . strtolower($this->option);
		$this->path['site']          = JPATH_ROOT . '/components/' . strtolower($this->option);
		$this->path['administrator'] = JPATH_ROOT . '/administrator/components/' . strtolower($this->option);

		define(strtoupper($this->name) . '_SELF',  $this->path['self']);
		define(strtoupper($this->name) . '_SITE',  $this->path['site']);
		define(strtoupper($this->name) . '_ADMIN', $this->path['administrator']);

		$this->container->registerServiceProvider(new ComponentProvider($this->name, $this));

		$task       = $this->input->getWord('task');
		$controller = $this->input->getWord('controller');

		if (!$task && !$controller)
		{
			$this->input->set('task',       $this->defaultController);
			$this->input->set('controller', $this->defaultController);
		}

		// Register form and fields
		\JForm::addFieldPath(WINDWALKER_SOURCE . '/Form/Fields');
		\JForm::addFormPath(WINDWALKER_SOURCE . '/Form/Forms');

		// Register elFinder controllers
		// @TODO: Should use event listener
		$this->registerTask('finder.elfinder.display', '\\Windwalker\\Elfinder\\Controller\\DisplayController');
		$this->registerTask('finder.elfinder.connect', '\\Windwalker\\Elfinder\\Controller\\ConnectController');
	}

	/**
	 * Register (map) a task to a method in the class.
	 *
	 * @param   string  $task        The task.
	 * @param   string  $controller  The name of the method in the derived class to perform for this task.
	 *
	 * @return  Component  A JControllerLegacy object to support chaining.
	 *
	 * @since   12.2
	 */
	public function registerTask($task, $controller)
	{
		$this->container->get('controller.resolver')->registerTask($task, $controller);

		return $this;
	}

	/**
	 * Unregister (unmap) a task in the class.
	 *
	 * @param   string  $task  The task.
	 *
	 * @return  Component  This object to support chaining.
	 *
	 * @since   12.2
	 */
	public function unregisterTask($task)
	{
		$this->container->get('controller.resolver')->unregisterTask($task);

		return $this;
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @param   string   $assetName   The asset name
	 * @param   integer  $categoryId  The category ID.
	 * @param   integer  $id          The item ID.
	 *
	 * @return  Object
	 *
	 * @since   3.1
	 */
	public function getActions($assetName, $categoryId = 0, $id = 0)
	{
		$user	= $this->container->get('user');
		$result	= new Object;

		$path = JPATH_ADMINISTRATOR . '/components/com_' . $this->name . '/access.xml';

		if (!$id && !$categoryId)
		{
			$section = 'component';
		}
		elseif (!$id && $categoryId)
		{
			$section = 'category';
			$assetName .= '.category.' . (int) $categoryId;
		}
		elseif ($id && !$categoryId)
		{
			$section = $assetName;
			$assetName .= '.' . $assetName . '.' . $id;
		}
		else
		{
			$section = $assetName;
			$assetName .= '.' . $assetName;
		}

		$actions = \JAccess::getActionsFromFile($path, "/access/section[@name='" . $section . "']/");

		foreach ($actions as $action)
		{
			$result->set($action->name, $user->authorise($action->name, $assetName));
		}

		return $result;
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

	/**
	 * getPath
	 *
	 * @param string $client
	 *
	 * @return string
	 */
	public function getPath($client = 'self')
	{
		$client = ($client == 'admin') ? 'administrator' : $client;

		return $this->path[$client];
	}

	/**
	 * getSitePath
	 *
	 * @return string
	 */
	public function getSitePath()
	{
		return $this->getPath('site');
	}

	/**
	 * getAdminPath
	 *
	 * @return string
	 */
	public function getAdminPath()
	{
		return $this->getPath('administrator');
	}
}
