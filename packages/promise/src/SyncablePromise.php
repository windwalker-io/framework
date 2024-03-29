<?php

declare(strict_types=1);

namespace Windwalker\Promise;

/**
 * The SyncablePromise class.
 */
class SyncablePromise extends Promise
{
    /**
     * @inheritDoc
     */
    protected function scheduleFor(callable $callback): void
    {
        $callback();
    }

    /**
     * @inheritDoc
     */
    protected function scheduleWait(): void
    {
        //
    }

    /**
     * @inheritDoc
     */
    protected function scheduleDone(): void
    {
        //
    }
}
