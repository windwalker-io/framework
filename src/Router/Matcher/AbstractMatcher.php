<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Router\Matcher;

use Windwalker\Router\Compiler\BasicCompiler;
use Windwalker\Router\Compiler\BasicGenerator;
use Windwalker\Router\Route;
use Windwalker\Router\RouteHelper;

/**
 * The AbstractMatcher class.
 *
 * @since  2.0
 */
abstract class AbstractMatcher implements MatcherInterface
{
	/**
	 * Property routes.
	 *
	 * @var Route[]
	 */
	protected $routes;

	/**
	 * Property count.
	 *
	 * @var  int
	 */
	protected $count = 0;

	/**
	 * Property debug.
	 *
	 * @var  boolean
	 */
	protected $debug = false;

	/**
	 * Property routeMaps.
	 *
	 * @var  array
	 */
	protected $routeMaps = array();

	/**
	 * build
	 *
	 * @param Route $route
	 * @param array $data
	 *
	 * @return  string
	 */
	public function build(Route $route, $data = array())
	{
		return BasicGenerator::generate($route->getPattern(), $data);
	}

	/**
	 * Match routes.
	 *
	 * @param string $route
	 * @param string $method
	 * @param array  $options
	 *
	 * @return  Route|false
	 */
	abstract public function match($route, $method = 'GET', $options = array());

	/**
	 * Match routes.
	 *
	 * @param string $route
	 * @param Route  $routeItem
	 *
	 * @return  Route|false
	 */
	public function matchRoute($route, Route $routeItem)
	{
		$regex = $routeItem->getRegex();

		if (!$regex || $this->debug)
		{
			$regex = BasicCompiler::compile($routeItem->getPattern(), $routeItem->getRequirements());

			$routeItem->setRegex($regex);
		}

		$route = RouteHelper::normalise($route);

		if (preg_match($regex, $route, $matches))
		{
			$variables = RouteHelper::getVariables($matches);

			$variables['_rawRoute'] = $route;
		}
		else
		{
			return false;
		}

		$routeItem->setVariables(array_merge($routeItem->getVariables(), $variables));

		return $routeItem;
	}

	/**
	 * Set Routes
	 *
	 * @param Route[] $routes
	 *
	 * @return  static
	 */
	public function setRoutes(array $routes)
	{
		$this->routes = $routes;

		return $this;
	}

	/**
	 * Method to get property RouteMaps
	 *
	 * @return  array
	 */
	public function getRouteMaps()
	{
		return $this->routeMaps;
	}

	/**
	 * Method to set property routeMaps
	 *
	 * @param   array $routeMaps
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setRouteMaps($routeMaps)
	{
		$this->routeMaps = $routeMaps;

		return $this;
	}

	/**
	 * matchOptions
	 *
	 * @param Route  $route
	 * @param string $method
	 * @param array  $options
	 *
	 * @return  bool
	 */
	protected function matchOptions(Route $route, $method, $options)
	{
		$options = $route->prepareOptions($options);

		// Match methods
		$this->checkType('method', $method, 'string');

		$allowMethods = $route->getAllowMethods();

		if ($allowMethods && !in_array(strtoupper($method), $allowMethods))
		{
			return false;
		}

		// Match Host
		$this->checkType('host', $options['host'], 'string');

		$host = $route->getHost();

		if ($host && $host != strtolower($options['host']))
		{
			return false;
		}

		// Match schemes
		$this->checkType('scheme', $options['scheme'], 'string');

		$scheme = $route->getScheme();

		if ($scheme && $scheme != strtolower($options['scheme']))
		{
			return false;
		}

		$port = $route->getPort();

		// Match port
		if (!$route->getSSL() && $port && $port != $options['port'])
		{
			return false;
		}

		if ($route->getSSL() && $port && $port != $options['port'])
		{
			return false;
		}

		return true;
	}

	/**
	 * checkType
	 *
	 * @param string $name
	 * @param mixed  $value
	 * @param string $type
	 * @param string $class
	 *
	 * @return  boolean
	 */
	protected function checkType($name, $value, $type = 'string', $class = null)
	{
		if ($value === null)
		{
			return true;
		}

		$type = strtolower($type);

		$false = false;

		if ($type == 'object')
		{
			if ($class && !is_subclass_of($value, $class))
			{
				$false = true;
			}
			elseif (!is_object($value))
			{
				$false = true;
			}
		}
		elseif (gettype($value) != $type)
		{
			$false = true;
		}

		if (!$false)
		{
			return true;
		}

		if ($class)
		{
			throw new \InvalidArgumentException(sprintf('%s should be instance of of %s.', $name, $class));
		}

		throw new \InvalidArgumentException(sprintf('%s should be type of %s.', $name, $type));
	}

	/**
	 * buildRouteMaps
	 *
	 * @param bool $refresh
	 *
	 * @return  static
	 */
	protected function buildRouteMaps($refresh = false)
	{
		if ($this->routeMaps && !$this->debug && !$refresh)
		{
			return $this;
		}

		foreach ($this->routes as $key => $routeItem)
		{
			$this->routeMaps[$routeItem->getName()] = $key;
		}

		return $this;
	}

	/**
	 * Method to get property Count
	 *
	 * @return  int
	 */
	public function getCount()
	{
		return $this->count;
	}

	/**
	 * Method to get property Debug
	 *
	 * @return  boolean
	 */
	public function getDebug()
	{
		return $this->debug;
	}

	/**
	 * Method to set property debug
	 *
	 * @param   boolean $debug
	 *
	 * @return  static  Return self to support chaining.
	 */
	public function setDebug($debug)
	{
		$this->debug = $debug;

		return $this;
	}
}
