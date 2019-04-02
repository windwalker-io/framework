<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Middleware;

/**
 * Middleware Interface
 *
 * @since 2.0
 */
interface MiddlewareInterface
{
    /**
     * Call next middleware.
     *
     * @param  mixed $data
     *
     * @return mixed
     */
    public function execute($data = null);

    /**
     * Get next middleware.
     *
     * @return  mixed|MiddlewareInterface
     */
    public function getNext();

    /**
     * Set next middleware.
     *
     * @param   object|MiddlewareInterface $callable The middleware object.
     *
     * @return  MiddlewareInterface  Return self to support chaining.
     */
    public function setNext($callable);
}
