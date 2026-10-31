<?php

declare(strict_types=1);

namespace Windwalker\Pool\Test\Stub;

/**
 * Test-only connection whose timestamps, ping and disconnect results can be controlled.
 *
 * Used by ConnectionPoolTest to exercise idle timeout, max lifetime, heartbeat and close error paths.
 * Safe to remove once those tests no longer exist.
 */
class StubAgingConnection extends StubConnection
{
    public ?int $lastTimeOverride = null;

    public ?int $createdTimeOverride = null;

    public bool $pingResult = true;

    public bool $disconnectThrows = false;

    public function getLastTime(): int
    {
        return $this->lastTimeOverride ?? parent::getLastTime();
    }

    public function getCreatedTime(): int
    {
        return $this->createdTimeOverride ?? parent::getCreatedTime();
    }

    public function ping(): bool
    {
        return $this->pingResult;
    }

    public function disconnect(): mixed
    {
        if ($this->disconnectThrows) {
            throw new \RuntimeException('Disconnect failed');
        }

        return parent::disconnect();
    }
}
