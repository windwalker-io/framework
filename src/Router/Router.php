<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Router;

/**
 * A path router.
 *
 * @since  1.0
 */
class Router extends AbstractRouter
{
	/**
	 * add
	 *
	 * @param string|Route $name
	 * @param string       $pattern
	 * @param array        $variables
	 * @param array        $method
	 *
	 * @throws  \InvalidArgumentException
	 * @return  Router
	 */
	public function addMap($name = null, $pattern = null, $variables = array(), $method = array())
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

			$route = new Route($pattern, $variables, $method);

			$route->setName($name);
		}

		return parent::addRoute($route);
	}
}
