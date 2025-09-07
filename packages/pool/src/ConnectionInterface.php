<?php

declare(strict_types=1);

namespace Windwalker\Pool;

use Windwalker\Pool\Enum\ConnectionState;

/**
 * Interface ConnectionInterface
 */
interface ConnectionInterface
{
    /**
     * setPool
     *
     * @param  ?PoolInterface  $pool
     *
     * @return  void
     */
    public function setPool(?PoolInterface $pool): void;

    /**
     * Create connection
     *
     * @return mixed
     */
    public function connect(): mixed;

    /**
     * Reconnect connection
     *
     * @return mixed
     */
    public function reconnect(): mixed;

    /**
     * Close connection
     *
     * @return mixed
     */
    public function disconnect(): mixed;

    /**
     * isConnected
     *
     * @return  bool
     */
    public function isConnected(): bool;

    /**
     * Get connection id
     *
     * @return int
     */
    public function getId(): int;

    /**
     * Release connection
     *
     * @param  bool  $force
     */
    public function release(bool $force = false): void;

    /**
     * Get last time
     *
     * @return int
     */
    public function getLastTime(): int;

    /**
     * Update last time
     */
    public function updateLastTime(): void;

    public function getCreatedTime(): int;

    public function getCurrentUses(): int;

    public function incrementUses(): int;

    /**
     * Set connection state.
     *
     * @param  ConnectionState  $state
     */
    public function setState(ConnectionState $state): void;

    /**
     * The connection state.
     *
     * @return  ConnectionState
     */
    public function getState(): ConnectionState;

    public function ping(): bool;
}
