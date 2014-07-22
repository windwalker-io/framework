<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Router;

/**
 * Class AbstractRouter
 *
 * @since 1.0
 */
abstract class AbstractRouter
{
	/**
	 * Property routes.
	 *
	 * @var  Route[]
	 */
	protected $routes = array();

	/**
	 * Property method.
	 *
	 * @var  string
	 */
	protected $method = 'GET';

	/**
	 * Class init.
	 *
	 * @param array $routes
	 */
	public function __construct($routes = array())
	{
		$this->routes = (array) $routes;
	}

	/**
	 * compile
	 *
	 * @return  AbstractRouter
	 */
	public function compile()
	{
		foreach ($this->routes as $route)
		{
			$route->compile();
		}

		return $this;
	}

	/**
	 * Add Route
	 *
	 * @param Route $route
	 *
	 * @throws  \InvalidArgumentException
	 * @return  AbstractRouter
	 */
	public function addRoute(Route $route)
	{
		if ($name = $route->getName())
		{
			$this->routes[$name] = $route;
		}
		else
		{
			$this->routes[] = $route;
		}

		return $this;
	}

	/**
	 * addRoutes
	 *
	 * @param array $routes
	 *
	 * @return  AbstractRouter
	 */
	public function addRoutes(array $routes)
	{
		foreach ($routes as $name => $route)
		{
			$this->addRoute($name, $route);
		}

		return $this;
	}

	/**
	 * parseRoute
	 *
	 * @param string $route
	 *
	 * @return  array|bool
	 *
	 * @throws \InvalidArgumentException
	 */
	public function parseRoute($route)
	{
		// Trim the query string off.
		$route = preg_replace('/([^?]*).*/u', '\1', $route);

		// Sanitize and explode the route.
		$route = trim(parse_url($route, PHP_URL_PATH), ' /');

		$route = $route ? : '/';

		$vars = false;

		foreach ($this->routes as $routeItem)
		{
			$routeItem->setMethod($this->method);

			$vars = $routeItem->match($route);

			if ($vars !== false)
			{
				break;
			}
		}

		if ($vars === false)
		{
			throw new \InvalidArgumentException(sprintf('Unable to handle request for route `%s`.', $route), 404);
		}

		return $vars;
	}

	/**
	 * buildRoute
	 *
	 * @param string $name
	 * @param array  $queries
	 *
	 * @return  string
	 *
	 * @throws \InvalidArgumentException
	 */
	public function buildRoute($name, $queries = array())
	{
		if (!array_key_exists($name, $this->routes))
		{
			throw new \InvalidArgumentException('Route: ' . $name . ' not found.');
		}

		return $this->routes[$name]->build($queries);
	}

	/**
	 * getMethod
	 *
	 * @return  string
	 */
	public function getMethod()
	{
		if (!$this->method)
		{
			$this->method = strtoupper($_SERVER['REQUEST_METHOD']);
		}

		return $this->method;
	}

	/**
	 * setMethod
	 *
	 * @param   string $method
	 *
	 * @return  AbstractRouter  Return self to support chaining.
	 */
	public function setMethod($method)
	{
		$this->method = strtoupper($method);

		return $this;
	}
}
 