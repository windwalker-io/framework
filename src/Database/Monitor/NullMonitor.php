<?php
/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2019 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Windwalker\Database\Monitor;

/**
 * The NullMonitor class.
 *
 * @since  __DEPLOY_VERSION__
 */
class NullMonitor implements QueryMonitorInterface
{
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
        //
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
        //
    }
}
