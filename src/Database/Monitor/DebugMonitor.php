<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Windwalker\Database\Monitor;

/**
 * The DebugMonitor class.
 *
 * @since  __DEPLOY_VERSION__
 */
class DebugMonitor implements QueryMonitorInterface
{
    /**
     * Property before.
     *
     * @var  callable
     */
    protected $before;

    /**
     * Property after.
     *
     * @var  callable
     */
    protected $after;

    /**
     * ProfilerMiddleware constructor.
     *
     * @param callable $before
     * @param callable $after
     */
    public function __construct(?callable $before = null, ?callable $after = null)
    {
        $this->setBefore($before)->setAfter($after);
    }

    /**
     * Start a query monitor.
     *
     * @param string $query The SQL to be executed.
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    public function start(string $query): void
    {
        call_user_func($this->getBefore(), [
            'query' => $query
        ]);
    }

    /**
     * Stop query monitor.
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    public function stop(): void
    {
        call_user_func($this->getAfter(), [

        ]);
    }

    /**
     * Method to set property before
     *
     * @param   callable $before
     *
     * @return  static  Return self to support chaining.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setBefore(callable $before): self
    {
        $this->before = $before;

        return $this;
    }

    /**
     * Method to set property after
     *
     * @param   callable $after
     *
     * @return  static  Return self to support chaining.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function setAfter(callable $after): self
    {
        $this->after = $after;

        return $this;
    }

    /**
     * Method to get property Before
     *
     * @return  callable
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getBefore(): callable
    {
        return $this->before ?: function () {};
    }

    /**
     * Method to get property After
     *
     * @return  callable
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getAfter(): callable
    {
        return $this->after ?: function () {};
    }
}
