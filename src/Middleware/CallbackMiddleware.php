<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Middleware;

/**
 * Callback Middleware
 *
 * @since 2.0
 */
class CallbackMiddleware extends AbstractMiddleware
{
    /**
     * The callback handler.
     *
     * @var  callable
     */
    protected $handler = null;

    /**
     * Constructor.
     *
     * @param callable $handler The callback handler.
     * @param object   $next    Next middleware.
     */
    public function __construct($handler = null, $next = null)
    {
        $this->handler = $handler;
        $this->next = $next;
    }

    /**
     * Call next middleware.
     *
     * @param  array $data
     *
     * @return mixed
     */
    public function execute($data = null)
    {
        return call_user_func($this->handler, $data, $this->next);
    }

    /**
     * Get callback handler.
     *
     * @return  callable The callback handler.
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * Set callback handler.
     *
     * @param   callable $handler The callback handler.
     *
     * @return  CallbackMiddleware  Return self to support chaining.
     */
    public function setHandler($handler)
    {
        $this->handler = $handler;

        return $this;
    }
}
