<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Promise\Scheduler;

use DomainException;
use React\EventLoop\LoopInterface;
use React\EventLoop\StreamSelectLoop;
use Swoole\Coroutine\Channel;
use Swoole\Event;

/**
 * The EventLoopScheduler class.
 */
class EventLoopScheduler implements SchedulerInterface
{
    /**
     * @var callable
     */
    protected $timerCallback;

    /**
     * EventLoopScheduler constructor.
     *
     * @param  callable|LoopInterface  $loop
     */
    public function __construct(callable|LoopInterface $loop)
    {
        $this->timerCallback = $loop instanceof LoopInterface
            ? self::createReactTimer($loop)
            : $loop;
    }

    /**
     * createReactTimer
     *
     * @param  LoopInterface  $loop
     *
     * @return  callable
     */
    public static function createReactTimer(LoopInterface $loop): callable
    {
        return static function () use ($loop) {
            $done = false;

            // Return schedule/waiter/doner
            return [
                static fn(callable $callable) => $loop->addTimer(0.001, $callable),
                static function () use (&$done) {
                    // todo: Truly support reactphp stream
                    // while (!$done) {
                    //     usleep(100);
                    // }
                },
                static function () use (&$done) {
                    $done = true;
                },
            ];
        };
    }

    /**
     * createSwooleTimer
     *
     * @param  int|null  $timeout
     *
     * @return  callable
     */
    public static function createSwooleTimer(?int $timeout = null): callable
    {
        if (!extension_loaded('swoole')) {
            throw new DomainException('Swoole not installed');
        }

        return static function () use ($timeout) {
            $scheduler = new SwooleScheduler($timeout);

            $cursor = $scheduler->createCursor();

            // Return schedule/waiter/doner
            return [
                fn (callable $callable) => $scheduler->schedule($cursor, $callable),
                fn () => $scheduler->wait($cursor),
                fn () => $scheduler->done($cursor),
            ];
        };
    }

    public static function swoole(?int $timeout = null): static
    {
        return new static(
            static::createSwooleTimer($timeout)
        );
    }

    /**
     * @inheritDoc
     */
    public static function isSupported(): bool
    {
        return true;
    }

    public function createCursor(): ScheduleCursor
    {
        $timerCallback = $this->timerCallback;

        return new ScheduleCursor(
            $timerCallback()
        );
    }

    /**
     * @param  ScheduleCursor  $cursor  *
     * @inheritDoc
     */
    public function schedule(ScheduleCursor $cursor, callable $callback): void
    {
        [$schedule] = $cursor->get();

        $schedule($callback);
    }

    /**
     * @inheritDoc
     */
    public function wait(ScheduleCursor $cursor): void
    {
        [, $waiter] = $cursor->get();

        $waiter();
    }

    /**
     * @inheritDoc
     */
    public function done(?ScheduleCursor $cursor): void
    {
        if ($cursor) {
            [,, $done] = $cursor->get();

            $done();
        }
    }

    public function release(ScheduleCursor $cursor): void
    {
        //
    }
}
