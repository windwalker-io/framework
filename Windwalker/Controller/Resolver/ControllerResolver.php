<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Controller\Resolver;

use Windwalker\DI\Container;
use Windwalker\Controller\Controller;

defined('_JEXEC') or die('Restricted access');

/**
 * Helper class for controllers.
 *
 * @since 3.2
 */
class ControllerResolver
{
	/**
	 * Property taskMapper.
	 *
	 * @var  array
	 */
	protected $taskMapper = array();

	/**
	 * Property application.
	 *
	 * @var  \JApplicationCms
	 */
	protected $application;

	/**
	 * Property container.
	 *
	 * @var  \Joomla\DI\Container
	 */
	protected $container;

	/**
	 * Constructor.
	 *
	 * @param \JApplicationCms $application
	 * @param Container        $container
	 */
	public function __construct(\JApplicationCms $application, Container $container)
	{
		$this->container   = $container;
		$this->application = $application;
	}

	/**
	 * Method to parse a controller from task string.
	 *
	 * @param   string        $prefix The class name prefix
	 * @param   \JInput       $input  Input request.
	 *
	 * @throws \RuntimeException
	 * @throws \Exception
	 *
	 * @return  Controller  A JController object
	 */
	public function getController($prefix, $input)
	{
		if ($controllerTask = $input->get('controller'))
		{
			// Temporary solution
			if (strpos($controllerTask, '/') !== false)
			{
				$tasks = explode('/', $controllerTask);
			}
			else
			{
				$tasks = explode('.', $controllerTask);
			}
		}
		else
		{
			// Checking for old MVC task
			$task = $input->get('task');

			// Toolbar expects old style but we are using new style
			// Remove when toolbar can handle either directly
			if (strpos($task, '/') !== false)
			{
				$tasks = explode('/', $task);
			}
			else
			{
				$tasks = explode('.', $task);
			}
		}

		if (!count($tasks) || empty($tasks[0]))
		{
			$tasks = array('Display');
		}

		$tasks = array_map('ucfirst', $tasks);

		$name = '';

		if (count($tasks) > 1)
		{
			$name = array_shift($tasks);
		}

		$controllerName = $this->resolveController($prefix, $name, implode('.', $tasks));

		// Config
		$config = array(
			'prefix' => strtolower($prefix),
			'name'   => strtolower($name),
			'task'   => strtolower(implode('.', $tasks)),
			'option' => 'com_' . strtolower($prefix)
		);

		/** @var $controller Controller */
		$controller = new $controllerName($input, $this->application, $config);

		return $controller;
	}

	/**
	 * resolveController
	 *
	 * @param string $prefix
	 * @param string $name
	 * @param string $task
	 *
	 * @return  string
	 *
	 * @throws \RuntimeException
	 * @throws \Exception
	 */
	public function resolveController($prefix, $name, $task)
	{
		$key = strtolower($name . '.' . $task);

		if (!empty($this->taskMapper[$key]))
		{
			return $this->taskMapper[$key];
		}

		$controllerName = '\\' . ucfirst($prefix) . 'Controller' . $name . str_replace('.', '', $task);

		if (!class_exists($controllerName))
		{
			$controllerName = '\\Windwalker\\Controller\\' . str_replace('.', '\\', $task) . 'Controller';

			if (!class_exists($controllerName))
			{
				if (JDEBUG)
				{
					throw new \RuntimeException(sprintf('Controller %s not found.', $controllerName));
				}
				else
				{
					throw new \Exception('Bad Route.', 404);
				}
			}
		}

		return $controllerName;
	}

	/**
	 * Register (map) a task to a method in the class.
	 *
	 * @param   string  $task        The task.
	 * @param   string  $controller  The name of the method in the derived class to perform for this task.
	 *
	 * @return  ControllerResolver  A JControllerLegacy object to support chaining.
	 *
	 * @since   12.2
	 */
	public function registerTask($task, $controller)
	{
		if (class_exists($controller))
		{
			$this->taskMapper[strtolower($task)] = $controller;
		}

		return $this;
	}

	/**
	 * Unregister (unmap) a task in the class.
	 *
	 * @param   string  $task  The task.
	 *
	 * @return  ControllerResolver  This object to support chaining.
	 *
	 * @since   12.2
	 */
	public function unregisterTask($task)
	{
		unset($this->taskMapper[strtolower($task)]);

		return $this;
	}
}
