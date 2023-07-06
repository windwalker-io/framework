<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Promise\Scheduler;

use LogicException;
use Swoole\Coroutine\Channel;
use Swoole\Event;

/**
 * The SwooleAsync class.
 */
class SwooleScheduler implements SchedulerInterface
{
    /**
     * @var int|null
     */
    protected ?int $timeout;

    /**
     * SwooleAsync constructor.
     *
     * @param  int|null  $timeout
     */
    public function __construct(?int $timeout = null)
    {
        $this->timeout = $timeout;
    }

    /**
     * @inheritDoc
     */
    public static function isSupported(): bool
    {
        return PHP_SAPI === 'cli' && extension_loaded('swoole') && function_exists('\go');
    }

    public function createCursor(): ScheduleCursor
    {
        return new ScheduleCursor(new Channel(1));
    }

    /**
     * @inheritDoc
     */
    public function schedule(ScheduleCursor $cursor, callable $callback): void
    {
        go(
            static function () use ($callback) {
                Event::defer(
                    static function () use ($callback) {
                        $callback();
                    }
                );
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function wait(ScheduleCursor $cursor): void
    {
        $chan = $cursor->get();

        if (!$chan instanceof Channel) {
            throw new LogicException('Cursor should be ' . Channel::class);
        }

        // SwooleScheduler should always called into coroutine,
        // so we don't need to create new coroutine to wrap $chan->pop().
        // This can make current coroutine blocked and wait for IO end.
        if ($this->timeout) {
            $chan->pop($this->timeout);
        } else {
            $chan->pop();
        }
    }

    /**
     * @inheritDoc
     */
    public function done(?ScheduleCursor $cursor): void
    {
        if (!$cursor) {
            return;
        }

        $chan = $cursor->get();

        if (!$chan instanceof Channel) {
            throw new LogicException('Cursor should be ' . Channel::class . ', got ' . get_debug_type($chan) . '.');
        }

        // Done() may be called at next event loop,
        // We create a new coroutine to make sure it always in coroutine context.
        go(
            static function () use ($chan) {
                $chan->push(true);
            }
        );
    }

    public function release(ScheduleCursor $cursor): void
    {
        /** @var Channel $channel */
        $channel = $cursor->get();

        if ($channel->length() > 0) {
            show('[WARNING] Channel released but not empty');
            trigger_error(
                'Channel released but not empty',
                E_USER_WARNING
            );
        }
    }
}
