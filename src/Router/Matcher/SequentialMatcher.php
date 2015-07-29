<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Router\Matcher;

use Windwalker\Router\Route;

/**
 * The SequentialMatcher class.
 * 
 * @since  2.0
 */
class SequentialMatcher extends AbstractMatcher
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
		$this->count = 0;

		foreach ($this->routes as $routeItem)
		{
			$this->count++;

			if (!$this->matchOptions($routeItem, $method, $options))
			{
				continue;
			}

			if ($this->matchRoute($route, $routeItem))
			{
				return $routeItem;
			}
		}

		return false;
	}
}
