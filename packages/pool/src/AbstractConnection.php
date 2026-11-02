<?php

declare(strict_types=1);

namespace Windwalker\Pool;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Windwalker\Pool\Enum\ConnectionState;
use Windwalker\Pool\Exception\ConnectionPoolException;

/**
 * The AbstractConnection class.
 */
abstract class AbstractConnection implements ConnectionInterface
{
    protected int $id = 0;

    protected ConnectionState $state = ConnectionState::INACTIVE;

    protected int $lastTime = 0;

    protected int $createdTime = 0;

    protected int $currentUses = 0;

    /**
     * The connection pool instance must store in WeakReference to prevent circular reference
     * and memory leak.
     *
     * @var  \WeakReference<AbstractPool>|null
     */
    protected ?\WeakReference $pool = null;

    /**
     * Whether to raise an E_USER_WARNING when a connection is destructed without being released
     * and no logger is available. If a logger exists, the leak is always sent to it regardless of this flag.
     *
     * @var  bool
     */
    public bool $leakWarning = true;

    /**
     * Receives the leak report. Falls back to the pool's logger, then to an E_USER_WARNING
     * (see {@see $leakWarning}).
     */
    public ?LoggerInterface $logger = null;

    /**
     * Set when PHP starts shutting down (including after exit), so leaked-connection warnings
     * are not raised for connections that the process is about to discard anyway.
     */
    private static bool $shuttingDown = false;

    private static bool $shutdownRegistered = false;

    public function __construct()
    {
        $this->createdTime = time();

        if (!self::$shutdownRegistered) {
            self::$shutdownRegistered = true;

            register_shutdown_function(static fn() => self::$shuttingDown = true);
        }
    }

    /**
     * @inheritDoc
     */
    public function setPool(?PoolInterface $pool): void
    {
        $this->pool = $pool ? \WeakReference::create($pool) : null;

        if ($pool !== null) {
            $this->id = $pool->getSerial();
        }
    }

    /**
     * @inheritDoc
     */
    public function reconnect(): mixed
    {
        $this->disconnect();

        return $this->connect();
    }

    /**
     * @inheritDoc
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function release(bool $force = false): void
    {
        if ($this->pool === null) {
            throw new ConnectionPoolException('No assigned pool of this connection.');
        }

        if ($this->state === ConnectionState::ACTIVE || $force) {
            $this->pool?->get()?->release($this);
        }
    }

    /**
     * @inheritDoc
     */
    public function getLastTime(): int
    {
        return $this->lastTime;
    }

    /**
     * @inheritDoc
     */
    public function updateLastTime(): void
    {
        $this->lastTime = time();
    }

    /**
     * @inheritDoc
     */
    public function setState(ConnectionState $state): void
    {
        $this->state = $state;
    }

    /**
     * Is connection active.
     *
     * @return  ConnectionState
     */
    public function getState(): ConnectionState
    {
        return $this->state;
    }

    public function __destruct()
    {
        if (
            $this->state !== ConnectionState::ACTIVE
            || !$this->pool
            || self::$shuttingDown
        ) {
            return;
        }

        $pool = $this->pool->get();
        $poolType = get_debug_type($pool);
        $logger = $this->logger;

        if ($logger === null && $pool instanceof AbstractPool && !$pool->logger instanceof NullLogger) {
            $logger = $pool->logger;
        }

        $message = sprintf(
            'Connection ID: %s in pool: %s was not released but destruct.',
            $this->getId(),
            $poolType
        );

        if ($logger !== null) {
            $logger->warning(
                $message,
                [
                    'connection_id' => $this->getId(),
                    'connection' => static::class,
                    'pool' => $poolType,
                    'last_time' => $this->getLastTime(),
                    'last_used_at' => date('Y-m-d H:i:s', $this->getLastTime()),
                    'uses' => $this->getCurrentUses(),
                ]
            );

            return;
        }

        if ($this->leakWarning) {
            trigger_error($message, E_USER_WARNING);
        }
    }

    public function getCreatedTime(): int
    {
        return $this->createdTime;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getCurrentUses(): int
    {
        return $this->currentUses;
    }

    public function incrementUses(): int
    {
        return ++$this->currentUses;
    }
}
