<?php
/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Http\Promise;

use Windwalker\Http\Response\Response;

/**
 * The PromiseResponse class.
 *
 * @since  3.4
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
     * @since  3.4
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
     * @since  3.4
     */
    public function fail(callable $callback)
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
     * @since  3.4
     */
    public function resolve($value)
    {
        try {
            foreach ($this->thenCallables as $then) {
                $value = $then($value);
            }
        } catch (\Exception $e) {
            foreach ($this->rejectCallables as $reject) {
                $e = $reject($e);
            }
        }

        return $value;
    }

    /**
     * reject
     *
     * @param \Exception|\Throwable $e
     *
     * @return  mixed
     *
     * @since  3.4.5
     */
    public function reject($e)
    {
        foreach ($this->rejectCallables as $reject) {
            $e = $reject($e);
        }

        return $e;
    }
}
