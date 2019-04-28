<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Router\Matcher;

use Windwalker\Router\Route;

/**
 * Interface MatcherInterface
 *
 * @since  2.0
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
    public function match($route, $method = 'GET', $options = []);

    /**
     * build
     *
     * @param Route $route
     * @param array $data
     *
     * @return  string
     */
    public function build(Route $route, $data = []);

    /**
     * Set Routes
     *
     * @param Route[] $routes
     *
     * @return  static
     */
    public function setRoutes(array $routes);
}
