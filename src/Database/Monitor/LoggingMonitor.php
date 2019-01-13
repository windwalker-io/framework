<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 $Asikart.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Database\Monitor;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

/**
 * The LoggingMonitor class.
 *
 * @since  3.5
 */
class LoggingMonitor implements QueryMonitorInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * DbLoggingMiddleware constructor.
     *
     * @param LoggerAwareInterface $logger
     */
    public function __construct(LoggerAwareInterface $logger = null)
    {
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * Start a query monitor.
     *
     * @param string $query The SQL to be executed.
     *
     * @return  void
     *
     * @since  3.5
     */
    public function start(string $query): void
    {
        $this->logger->info('Executed: {sql}', ['sql' => $query]);
    }

    /**
     * Stop query monitor.
     *
     * @return  void
     *
     * @since  3.5
     */
    public function stop(): void
    {
        //
    }
}
