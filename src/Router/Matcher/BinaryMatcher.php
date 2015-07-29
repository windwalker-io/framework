<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Router\Matcher;

use Windwalker\Router\Route;
use Windwalker\Router\RouteHelper;

/**
 * The BinaryMatcher class.
 * 
 * @since  2.0
 */
class BinaryMatcher extends AbstractMatcher
{
	/**
	 * Match routes.
	 *
	 * @param string $route
	 * @param string $method
	 * @param array  $options
	 *
	 * @return  Route|false
	 */
	public function match($route, $method = 'GET', $options = array())
	{
		$route = RouteHelper::normalise($route);

		$this->count = 0;

		$this->buildRouteMaps();

		$keys = array_keys($this->routeMaps);

		$left = 0;
		$right = count($this->routeMaps) - 1;

		while ($left <= $right)
		{
			$middle    = round(($left + $right) / 2);
			$key       = $keys[$middle];
			$routeItem = $this->routes[$this->routeMaps[$key]];

			$this->count++;

			if ($this->matchOptions($routeItem, $method, $options) && $this->matchRoute($route, $routeItem))
			{
				return $routeItem;
			}

			if (strcmp($route, $key) < 0)
			{
				$right = $middle - 1;
			}
			else
			{
				$left = $middle + 1;
			}
		}

		return false;
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
			$this->routeMaps[$routeItem->getPattern()] = $key;
		}

		ksort($this->routeMaps);

		return $this;
	}
}
