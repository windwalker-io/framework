<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

namespace Windwalker\Router\Matcher;

use Windwalker\Router\Route;

/**
 * Interface MatcherInterface
 *
 * @since  {DEPLOY_VERSION}
 */
interface MatcherInterface
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
	public function match($route, $method = 'GET', $options = array());

	/**
	 * build
	 *
	 * @param Route $route
	 * @param array $data
	 *
	 * @return  string
	 */
	public function build(Route $route, $data = array());

	/**
	 * Set Routes
	 *
	 * @param Route[] $routes
	 *
	 * @return  static
	 */
	public function setRoutes(array $routes);
}
