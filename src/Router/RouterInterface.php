<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Router;

/**
 * Interface RouterInterface
 */
interface RouterInterface
{
    /**
     * match
     *
     * @param string $route
     *
     * @return  mixed
     *
     * @throws \InvalidArgumentException
     */
    public function match($route);
}
