<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Joomla.Libraries
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Windwalker\Controller;

defined('_JEXEC') or die('Restricted access');

/**
 * Helper class for controllers.
 *
 * @since 3.2
 */
class ControllerHelper
{
	const CONTROLLER_PREFIX = 0;
	const CONTROLLER_ACTIVITY = 2;
	const CONTROLLER_VIEW_FOLDER = 1;

	/**
	 * Method to parse a controller from a url.
	 *
	 * Defaults to the base controllers and passes an array of options.
	 *      $options[0] is the location of the controller which defaults to the core libraries (referenced as 'j'
	 *      and then the named folder within the component entry point file.
	 *      $options[1] is the name of the controller file,
	 *      $options[2] is the name of the folder found in the component controller folder for controllers
	 *      not prefixed with J.
	 *      Additional options maybe added to parameterise the controller.
	 *
	 * @param   \JApplication  $app  A JApplication object (could be JApplication or JApplicationWeb)
	 *
	 * @return  \JController  A JController object
	 *
	 * @since  3.2
	 */
	public static function getController($prefix, $input, $app)
	{
		if ($controllerTask = $app->input->get('controller'))
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
			$task = $app->input->get('task');

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

		$controllerName = '\\' . ucfirst($prefix) . 'Controller' . $name . implode($tasks);

		if (!class_exists($controllerName))
		{
			$controllerName = '\\Windwalker\\Controller\\' . implode($tasks) . 'Controller';

			if (!class_exists($controllerName))
			{
				throw new \RuntimeException(sprintf('Controller %s not found.', $controllerName));
			}
		}

		$controller = new $controllerName($input, $app);

		$controller->setPrefix($prefix)
			->setName($name);

		return $controller;
	}
}
