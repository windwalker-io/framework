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

    public function createCursor(): ScheduleCursor;

    /**
     * runAsync
     *
     * @param  ScheduleCursor  $cursor
     * @param  callable        $callback
     *
     * @return  void
     */
    public function schedule(ScheduleCursor $cursor, callable $callback): void;

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
}
