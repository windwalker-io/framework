<?php

declare(strict_types=1);

namespace Windwalker\Promise\Test\Scheduler;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Windwalker\Promise\Scheduler\TaskQueue;

class TaskQueueTest extends TestCase
{
    #[Test]
    public function runExecutesTasksInPushOrder(): void
    {
        $queue = new TaskQueue(false);
        $order = [];

        $queue->push(function () use (&$order) {
            $order[] = 'a';
        });
        $queue->push(function () use (&$order) {
            $order[] = 'b';
        });

        $queue->run();

        self::assertSame(['a', 'b'], $order);
    }

    #[Test]
    public function runExecutesTasksPushedByRunningTasks(): void
    {
        $queue = new TaskQueue(false);
        $order = [];

        $queue->push(function () use ($queue, &$order) {
            $order[] = 'outer';

            $queue->push(function () use (&$order) {
                $order[] = 'inner';
            });
        });

        $queue->run();

        self::assertSame(['outer', 'inner'], $order);
    }

    #[Test]
    public function runThrowsWhenQueueIsEmpty(): void
    {
        $this->expectException(RuntimeException::class);

        (new TaskQueue(false))->run();
    }

    #[Test]
    public function clearReturnsPendingTasksAndEmptiesQueue(): void
    {
        $queue = new TaskQueue(false);
        $task = static function () {};

        $queue->push($task);

        self::assertSame([$task], $queue->clear());
        self::assertSame([], $queue->clear());
    }

    #[Test]
    public function getInstanceReturnsSharedInstanceUntilReplaced(): void
    {
        $original = TaskQueue::getInstance();
        $replacement = new TaskQueue(false);

        try {
            self::assertSame($original, TaskQueue::getInstance());
            self::assertSame($replacement, TaskQueue::getInstance($replacement));
            self::assertSame($replacement, TaskQueue::getInstance());
        } finally {
            TaskQueue::getInstance($original);
        }
    }
}
