<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Router;

/**
 * Interface RouterInterface.
 */
interface RouterInterface
{
    /**
     * match.
     *
     * @param string $route
     *
     * @throws \InvalidArgumentException
     *
     * @return mixed
     */
    public function match($route);
}
