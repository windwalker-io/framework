<?php

declare(strict_types=1);

namespace Windwalker\Pool;

use Countable;
use Swoole\Thread\Pool;

/**
 * Interface PoolInterface
 */
interface PoolInterface extends Countable
{
    /**
     * @deprecated  Use {@see PoolOptions} instead.
     */
    public const string MAX_SIZE = 'max_size';

    /**
     * @deprecated  Use {@see PoolOptions} instead.
     */
    public const string MIN_SIZE = 'min_size';

    /**
     * @deprecated  Use {@see PoolOptions} instead.
     */
    public const string MAX_WAIT = 'max_wait';

    /**
     * @deprecated  Use {@see PoolOptions} instead.
     */
    public const string WAIT_TIMEOUT = 'wait_timeout';

    /**
     * @deprecated  Use {@see PoolOptions} instead.
     */
    public const string IDLE_TIMEOUT = 'idle_timeout';

    /**
     * @deprecated  Use {@see PoolOptions} instead.
     */
    public const string CLOSE_TIMEOUT = 'close_timeout';

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
