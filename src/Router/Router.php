<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Router;

use Windwalker\Router\Exception\RouteNotFoundException;
use Windwalker\Router\Matcher\MatcherInterface;
use Windwalker\Router\Matcher\SequentialMatcher;

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
	 * Property matcher.
	 *
	 * @var  MatcherInterface
	 */
	protected $matcher;

	/**
	 * Class init.
	 *
	 * @param array            $routes
	 * @param MatcherInterface $matcher
	 */
	public function __construct(array $routes = array(), MatcherInterface $matcher = null)
	{
		$this->addRoutes($routes);

		$this->matcher = $matcher ? : new SequentialMatcher;
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
	 * @param string|Route $name
	 * @param string       $pattern
	 * @param array        $variables
	 * @param array        $method
	 * @param array        $options
	 *
	 * @return  Router
	 */
	public function addRoute($name, $pattern = null, $variables = array(), $method = array(), $options = array())
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

			$route = new Route($name, $pattern, $variables, $method, $options);
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
		foreach ($routes as $route)
		{
			$this->addRoute($route);
		}

		return $this;
	}

	/**
	 * parseRoute
	 *
	 * @param string $route
	 * @param string $method
	 * @param array  $options
	 *
	 * @return  array|boolean
	 */
	public function match($route, $method = 'GET', $options = array())
	{
		// Trim the query string off.
		$route = preg_replace('/([^?]*).*/u', '\1', $route);

		// Sanitize and explode the route.
		$route = trim(parse_url($route, PHP_URL_PATH), ' /');

		$route = $route ? : '/';

		$matched = $this->matcher
			->setRoutes($this->routes)
			->match($route, $method, $options);

		if ($matched === false)
		{
			throw new RouteNotFoundException(sprintf('Unable to handle request for route `%s`.', $route), 404);
		}

		return $matched->getVariables();
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

		return $this->matcher->build($this->routes[$name], $queries);
	}

	/**
	 * Method to get property Matcher
	 *
	 * @return  MatcherInterface
	 */
	public function getMatcher()
	{
		return $this->matcher;
	}

	/**
	 * Method to set property matcher
	 *
	 * @param   MatcherInterface $matcher
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setMatcher($matcher)
	{
		$this->matcher = $matcher;

		return $this;
	}

	/**
	 * Method to get property Routes
	 *
	 * @return  Route[]
	 */
	public function getRoutes()
	{
		return $this->routes;
	}

	/**
	 * Method to set property routes
	 *
	 * @param   Route[] $routes
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setRoutes($routes)
	{
		$this->routes = $routes;

		return $this;
	}
}
