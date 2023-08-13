<?php

/**
 * Part of Windwalker Packages project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Pool;

use Countable;

/**
 * Interface PoolInterface
 */
interface PoolInterface extends Countable
{
    public const MAX_SIZE = 'max_size';

    public const MIN_SIZE = 'min_size';

    public const MAX_WAIT = 'max_wait';

    public const WAIT_TIMEOUT = 'wait_timeout';

    public const IDLE_TIMEOUT = 'idle_timeout';

    public const CLOSE_TIMEOUT = 'close_timeout';

    /**
     * Initialize pool
     */
    public function init(): void;

    /**
     * Create connection
     *
     * @return ConnectionInterface
     */
    public function createConnection(): ConnectionInterface;

    /**
     * Get connection from pool
     *
     * @return ConnectionInterface
     */
    public function getConnection(): ConnectionInterface;

    /**
     * dropConnection
     *
     * @param  ConnectionInterface  $connection
     *
     * @return  void
     */
    public function dropConnection(ConnectionInterface $connection): void;

    /**
     * Release connection to pool
     *
     * @param  ConnectionInterface  $connection
     */
    public function release(ConnectionInterface $connection): void;

    /**
     * @return int
     */
    public function getSerial(): int;

    /**
     * Close connections
     *
     * @return int
     */
    public function close(): int;
}
