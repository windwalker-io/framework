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
 * Interface AsyncInterface
 */
interface SchedulerInterface
{
    /**
     * isSupported
     *
     * @return  bool
     */
    public static function isSupported(): bool;

    /**
     * runAsync
     *
     * @param  callable  $callback
     *
     * @return  ScheduleCursor
     */
    public function schedule(callable $callback): ScheduleCursor;

    /**
     * wait
     *
     * @param  ScheduleCursor  $cursor
     *
     * @return  void
     */
    public function wait(ScheduleCursor $cursor): void;

    /**
     * done
     *
     * @param  ScheduleCursor  $cursor
     *
     * @return  void
     */
    public function done(?ScheduleCursor $cursor): void;

    /**
     * Release cursor from memory.
     *
     * @param  ScheduleCursor  $cursor
     *
     * @return  void
     */
    public function release(ScheduleCursor $cursor): void;
}
