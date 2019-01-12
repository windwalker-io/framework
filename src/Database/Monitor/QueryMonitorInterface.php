<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

namespace Windwalker\Database\Monitor;


/**
 * Interface QueryMonitorInterface
 *
 * @since  __DEPLOY_VERSION__
 */
interface QueryMonitorInterface
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
    public function start(string $query): void;

    /**
     * Stop query monitor.
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    public function stop(): void;
}
