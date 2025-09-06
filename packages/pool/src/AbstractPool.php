<?php

declare(strict_types=1);

namespace Windwalker\Pool;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Swoole\Coroutine;
use Throwable;
use Windwalker\Pool\Exception\ConnectionPoolException;
use Windwalker\Pool\Stack\SingleStack;
use Windwalker\Pool\Stack\StackInterface;

/**
 * The AbstractPool class.
 */
abstract class AbstractPool implements PoolInterface
{
    /**
     * @var StackInterface|null
     */
    protected ?StackInterface $stack = null;

    /**
     * @var bool
     */
    protected bool $init = false;

    /**
     * @var int
     */
    protected int $serial = 0;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var int
     */
    protected int $totalCount = 0;

    public protected(set) PoolOptions $options;

    /**
     * AbstractPool constructor.
     *
     * @param  array|PoolOptions     $options
     * @param  StackInterface|null   $stack
     * @param  LoggerInterface|null  $logger
     */
    public function __construct(
        array|PoolOptions $options = [],
        ?StackInterface $stack = null,
        ?LoggerInterface $logger = null
    ) {
        $this->options = PoolOptions::wrapWith($options);

        $this->stack = $stack ?? $this->createStack();
        $this->logger = $logger ?? new NullLogger();
    }

    protected function createStack(): StackInterface
    {
        return new SingleStack();
    }

    /**
     * @param  LoggerInterface  $logger
     *
     * @return  static  Return self to support chaining.
     */
    public function setLogger(LoggerInterface $logger): static
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function init(): void
    {
        if ($this->init === true) {
            return;
        }

        if (!$this->stack instanceof SingleStack) {
            for ($i = 0; $i < $this->options->maxSize; $i++) {
                $this->createConnection();
            }
        } else {
            $this->createConnection();
        }

        $this->init = true;
    }

    /**
     * @inheritDoc
     */
    public function createConnection(): ConnectionInterface
    {
        $this->totalCount++;

        $connection = $this->create();
        $connection->setPool($this);
        $connection->updateLastTime();
        $connection->release(true);

        $this->logger->info("Connection created: {$connection->getId()}");

        return $connection;
    }

    /**
     * Create a new connection.
     *
     * @return  ConnectionInterface
     */
    abstract protected function create(): ConnectionInterface;

    /**
     * @inheritDoc
     */
    public function dropConnection(ConnectionInterface $connection): void
    {
        $this->totalCount--;
        $connection->disconnect();
        $connection->setPool(null);
    }

    /**
     * pop
     *
     * @param  int|null  $timeout
     *
     * @return  ConnectionInterface
     */
    protected function pop(?int $timeout = null): ConnectionInterface
    {
        return $this->popPreprocess($this->stack->pop($timeout));
    }

    protected function popPreprocess(ConnectionInterface $connection): ConnectionInterface
    {
        $connection->updateLastTime();
        $connection->incrementUses();
        $connection->setActive(true);

        return $connection;
    }

    protected function createAndPop(): ConnectionInterface
    {
        $this->createConnection();

        return $this->pop();
    }

    /**
     * @inheritDoc
     */
    public function getConnection(): ConnectionInterface
    {
        // Less than min active
        if ($this->totalCount() < $this->options->minSize) {
            return $this->createAndPop();
        }

        // Pop connections
        $connection = null;

        if ($this->count() !== 0 && $connection = $this->clearExpiredAndPop()) {
            // Found a connection, return it.
            return $connection;
        }

        // If no connections found, stack is empty
        // and if not reach max active number, create a new one.
        if ($this->totalCount() < $this->options->maxSize) {
            return $this->createAndPop();
        }

        $maxWait = $this->options->maxWait;

        if ($maxWait > 0 && $this->stack->waitingCount() >= $maxWait) {
            throw new ConnectionPoolException(
                sprintf(
                    'Waiting Consumer is full. max_wait=%d count=%d',
                    $maxWait,
                    $this->stack->count()
                )
            );
        }

        return $this->pop($this->options->waitTimeout ?? -1);
    }

    /**
     * @inheritDoc
     */
    public function release(ConnectionInterface $connection): void
    {
        if ($this->isReachMaxUses($connection)) {
            $this->dropConnection($connection);

            $this->logger->info("Connection reach max uses and disconnected: {$connection->getId()}");

            return;
        }

        if ($this->stack->count() < $this->options->minSize) {
            $connection->setActive(false);
            $this->stack->push($connection);

            return;
        }

        // Disconnect then drop it.
        $this->dropConnection($connection);
    }

    /**
     * @inheritDoc
     */
    public function getSerial(): int
    {
        return ++$this->serial;
    }

    /**
     * @inheritDoc
     */
    public function close(): int
    {
        if ($this->stack === null) {
            return 0;
        }

        $length = $closed = $this->count();

        while ($length) {
            $connection = $this->stack->pop($this->options->closeTimeout);

            try {
                $this->dropConnection($connection);
            } catch (Throwable $e) {
                $this->logger->warning(
                    sprintf(
                        'Error while closing connection: %s',
                        $e->getMessage()
                    )
                );
            }

            $this->logger->info("Connection closed: {$connection->getId()}");

            $length--;
        }

        return $closed;
    }

    protected function clearExpiredAndPop(): ?ConnectionInterface
    {
        $time = time();

        while ($this->stack->count() !== 0) {
            $connection = $this->stack->pop();

            $lastTime = $connection->getLastTime();

            // If out of max idle time, drop this connection.
            if ($this->options->idleTimeout > 0 && ($time - $lastTime) > $this->options->idleTimeout) {
                $this->dropConnection($connection);

                $this->logger->info("Connection reach max idle timeout and disconnected: {$connection->getId()}");
                continue;
            }

            // If out of max lifetime, drop this connection.
            if (
                $this->options->maxLifetime > 0
                && ($time - $connection->getCreatedTime()) > $this->options->maxLifetime
            ) {
                $this->dropConnection($connection);

                $this->logger->info("Connection reach max lifetime and disconnected: {$connection->getId()}");
                continue;
            }

            return $this->popPreprocess($connection);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return $this->stack->count();
    }

    /**
     * totalCount
     *
     * @return  int
     */
    public function totalCount(): int
    {
        return $this->totalCount;
    }

    protected function isReachMaxUses(ConnectionInterface $connection): bool
    {
        $uses = $connection->getCurrentUses();

        if ($uses === PHP_INT_MAX) {
            return true;
        }

        return $this->options->maxUses > 0 && $uses >= $this->options->maxUses;
    }

    public function __destruct()
    {
        if (class_exists(Coroutine::class) && Coroutine::getCid() === -1) {
            return;
        }

        $this->close();
    }
}
