<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Promise\Scheduler;

/**
 * The ImmediateScheduler class.
 *
 * Note this Scheduler will run callback instantly when you call any then() or catch(),
 * It means that any rejection or error will instantly throw if you call `->then(callback)`.
 *
 * For Example, this will get an UncaughtException:
 *
 * ```php
 * Promise::rejected('...')
 *     ->then(fn() => ...) // Instantly throw exception.
 *     ->catch(fn() => ...) // catch() not work,
 * ```
 *
 * Instead, you can use onRejected callback.
 *
 * ```php
 * Promise::rejected('...')
 *     ->then(fn() => ..., fn() => ); // The second params (rejection callback) will work, no exception.
 * ```
 */
class ImmediateScheduler implements SchedulerInterface
{
    /**
     * @inheritDoc
     */
    public static function isSupported(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function schedule(callable $callback): ScheduleCursor
    {
        $callback();

        return new ScheduleCursor();
    }

    /**
     * @inheritDoc
     */
    public function wait(ScheduleCursor $cursor): void
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function done(?ScheduleCursor $cursor): void
    {
        //
    }
}
