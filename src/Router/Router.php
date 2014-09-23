<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Router;

/**
 * A path router.
 *
 * @since  {DEPLOY_VERSION}
 */
class Router
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
	public function __construct(array $routes = array())
	{
		$this->addRoutes($routes);
	}

	/**
	 * addMap
	 *
	 * @param string $pattern
	 * @param array  $variables
	 *
	 * @return  Route
	 */
	public function addMap($pattern, $variables = array())
	{
		$route = new Route(null, $pattern, $variables);

		$this->addRoute($route);

		return $route;
	}

	/**
	 * addMaps
	 *
	 * @param array $maps
	 *
	 * @return  $this
	 */
	public function addMaps(array $maps)
	{
		foreach ($maps as $pattern => $variables)
		{
			$this->addMap($pattern, $variables);
		}

		return $this;
	}

	/**
	 * Add Route
	 *
	 * @param string|Route  $name
	 * @param string        $pattern
	 * @param array         $variables
	 * @param array         $method
	 *
	 * @throws \InvalidArgumentException
	 *
	 * @return  Router
	 */
	public function addRoute($name, $pattern = null, $variables = array(), $method = array())
	{
		if ($name instanceof Route)
		{
			$route = $name;
		}
		else
		{
			if (!is_string($pattern))
			{
				throw new \InvalidArgumentException('Route pattern should be string');
			}

			$route = new Route($name, $pattern, $variables, $method);
		}

		if ($name = $route->getName())
		{
			$this->routes[$name] = $route;
		}
		elseif (!$name || is_numeric($name))
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
	 * @return  Router
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
	 * compile
	 *
	 * @return  Router
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
	 * parseRoute
	 *
	 * @param string $route
	 *
	 * @return  array|boolean
	 *
	 * @throws \InvalidArgumentException
	 */
	public function match($route)
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
	public function build($name, $queries = array())
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
	 * @return  static  Return self to support chaining.
	 */
	public function setMethod($method)
	{
		$this->method = strtoupper($method);

		return $this;
	}
}
