<?php
/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2018 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Windwalker\Http\Promise;

use Windwalker\Http\Response\Response;

/**
 * The PromiseResponse class.
 *
 * @since  __DEPLOY_VERSION__
 */
class PromiseResponse extends Response
{
    /**
     * Property thenCallables.
     *
     * @var  callable[]
     */
    protected $thenCallables = [];

    /**
     * Property rejectCallables.
     *
     * @var  callable[]
     */
    protected $rejectCallables = [];

    /**
     * then
     *
     * @param callable $callback
     *
     * @return  static
     *
     * @since  __DEPLOY_VERSION__
     */
    public function then(callable $callback)
    {
        $this->thenCallables[] = $callback;

        return $this;
    }

    /**
     * reject
     *
     * @param callable $callback
     *
     * @return  static
     *
     * @since  __DEPLOY_VERSION__
     */
    public function reject(callable $callback)
    {
        $this->rejectCallables[] = $callback;

        return $this;
    }

    /**
     * resolve
     *
     * @param mixed $value
     *
     * @return  mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    public function resolve($value)
    {
        try {
            foreach ($this->thenCallables as $then) {
                $value = $then($value);
            }
        } catch (\Exception $e) {
            foreach ($this->rejectCallables as $reject) {
                $reject($e);
            }
        }

        return $value;
    }
}
