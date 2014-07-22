<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Router;

/**
 * Class SingleActionRouter
 *
 * @since 1.0
 */
class SingleActionRouter extends AbstractRouter
{
	/**
	 * Property requests.
	 *
	 * @var  array
	 */
	protected $requests = array();

	/**
	 * addRoute
	 *
	 * @param string $pattern
	 * @param string $controller
	 * @param array  $method
	 *
	 * @throws  \LogicException
	 * @throws  \InvalidArgumentException
	 * @return  AbstractRouter
	 */
	public function addMap($pattern, $controller, $method = array())
	{
		if (!is_string($controller))
		{
			throw new \InvalidArgumentException('Please give me controller name.');
		}

		if ($pattern instanceof Route)
		{
			throw new \LogicException('Do not use Route object in ' . get_called_class());
		}

		$route = new Route($pattern, array('_controller' => $controller), $method);

		return parent::addRoute($route);
	}

	/**
	 * addRoute
	 *
	 * @param Route $route
	 *
	 * @return  void|AbstractRouter
	 *
	 * @throws \LogicException
	 */
	public function addRoute(Route $route)
	{
		throw new \LogicException('Do not use addRoute() in ' . get_called_class());
	}

	/**
	 * parseRoute
	 *
	 * @param string $route
	 *
	 * @throws  \UnexpectedValueException
	 * @return  array|boolean
	 */
	public function parseRoute($route)
	{
		$vars = parent::parseRoute($route);

		if (!array_key_exists('_controller', $vars))
		{
			throw new \UnexpectedValueException('Controller not found.', 500);
		}

		$controller = $vars['_controller'];

		unset($vars['_controller']);

		$this->setRequests($vars);

		return $controller;
	}

	/**
	 * getRequests
	 *
	 * @return  array
	 */
	public function getRequests()
	{
		return $this->requests;
	}

	/**
	 * setRequests
	 *
	 * @param   array $requests
	 *
	 * @return  SingleActionRouter  Return self to support chaining.
	 */
	public function setRequests($requests)
	{
		$this->requests = $requests;

		return $this;
	}
}
 