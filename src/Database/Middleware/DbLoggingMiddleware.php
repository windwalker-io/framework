<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Database\Middleware;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Windwalker\Database\Driver\AbstractDatabaseDriver;
use Windwalker\Middleware\AbstractMiddleware;

/**
 * The DbLoggingMiddleware class.
 *
 * @since  3.0
 */
class DbLoggingMiddleware extends AbstractMiddleware implements LoggerAwareInterface
{
    /**
     * Property logger.
     *
     * @var  LoggerAwareInterface
     */
    protected $logger;

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
     * Call next middleware.
     *
     * @param  \stdClass $data
     *
     * @return mixed
     */
    public function execute($data = null)
    {
        if (!isset($data->db) || !$data->db instanceof AbstractDatabaseDriver) {
            return $this->next->execute($data);
        }

        if ($data->debug) {
            $this->logger->log(LogLevel::DEBUG, 'Executed: {sql}', ['sql' => $data->sql]);
        }

        try {
            $result = $this->next->execute($data);
        } catch (\RuntimeException $e) {
            // Throw the normal query exception.
            $this->logger->log(LogLevel::ERROR, 'Database query failed (error #{code}): {message}',
                ['code' => $e->getCode(), 'message' => $e->getMessage()]);

            throw new $e();
        }

        return $result;
    }

    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger
     *
     * @return static
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }
}
