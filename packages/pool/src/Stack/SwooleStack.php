<?php

declare(strict_types=1);

namespace Windwalker\Pool\Stack;

use LogicException;
use Swoole\Coroutine\Channel;
use Swoole\Timer;
use Windwalker\Pool\ConnectionInterface;
use Windwalker\Pool\Exception\WaitTimeoutException;

/**
 * The SwooleDriver class.
 */
class SwooleStack implements StackInterface, TimerSupportedInterface
{
    protected int $maxSize = 1;

    protected ?Channel $channel = null;

    protected ?int $timer = null;

    /**
     * SwooleDriver constructor.
     *
     * @param  int  $maxSize
     */
    public function __construct(int $maxSize = 10)
    {
        $this->maxSize = $maxSize;

        $this->channel ??= new Channel($this->maxSize);
    }

    /**
     * @inheritDoc
     */
    public function push(ConnectionInterface $connection): void
    {
        $this->channel->push($connection);
    }

    /**
     * @inheritDoc
     */
    public function pop(?int $timeout = null): ConnectionInterface
    {
        if (!$this->channel) {
            throw new LogicException('Channel not exists in ' . static::class);
        }

        $conn = $this->channel->pop($timeout ?? -1);

        if ($conn === false) {
            throw new WaitTimeoutException('Wait connection timeout or channel closed.');
        }

        return $conn;
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return $this->channel->length();
    }

    /**
     * @inheritDoc
     */
    public function waitingCount(): int
    {
        return $this->channel->stats()['consumer_num'] ?? 0;
    }

    public function startTimer(\Closure $handler, int $intervalSeconds): void
    {
        if ($this->timer) {
            return;
        }

        $this->timer = Timer::tick(
            $intervalSeconds * 1000,
            $handler
        );
    }

    public function stopTimer(): void
    {
        Timer::clear($this->timer);

        $this->timer = null;
    }
}
