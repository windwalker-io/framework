<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Iterator;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SplPriorityQueue;
use Windwalker\Utilities\Iterator\PriorityQueue;

class PriorityQueueTest extends TestCase
{
    /**
     * @see  PriorityQueue::insert
     */
    #[Test]
    public function insertKeepsInsertionOrderForSamePriority(): void
    {
        $queue = new PriorityQueue();
        $queue->insert('a', PriorityQueue::NORMAL);
        $queue->insert('b', PriorityQueue::NORMAL);
        $queue->insert('c', PriorityQueue::HIGH);
        $queue->insert('d', PriorityQueue::LOW);

        self::assertSame(['c', 'a', 'b', 'd'], $queue->toArray());
    }

    /**
     * @see  PriorityQueue::insert
     */
    #[Test]
    public function insertAcceptsArrayPriorityAsMultiLevelKey(): void
    {
        $queue = new PriorityQueue();
        $queue->insert('a', [1, 1]);
        $queue->insert('b', [1, 2]);
        $queue->insert('c', [0, 9]);

        self::assertSame(['b', 'a', 'c'], $queue->toArray());
    }

    /**
     * @see  PriorityQueue::__construct
     * @see  PriorityQueue::join
     */
    #[Test]
    public function constructorJoinsItemsWithGivenPriority(): void
    {
        $queue = new PriorityQueue(['a', 'b'], PriorityQueue::HIGH);
        $queue->join(['c'], PriorityQueue::MAX);

        self::assertSame(['c', 'a', 'b'], $queue->toArray());
    }

    /**
     * @see  PriorityQueue::insertArray
     */
    #[Test]
    public function insertArrayUsesKeysAsPriority(): void
    {
        $queue = (new PriorityQueue())->insertArray([10 => 'low', 50 => 'high', 30 => 'mid']);

        self::assertSame(['high', 'mid', 'low'], $queue->toArray());
    }

    /**
     * @see  PriorityQueue::__construct
     * @see  PriorityQueue::merge
     */
    #[Test]
    public function constructorMergesAnotherSplPriorityQueue(): void
    {
        $spl = new SplPriorityQueue();
        $spl->insert('x', 1);
        $spl->insert('y', 5);

        $queue = new PriorityQueue($spl);

        self::assertSame(['y', 'x'], $queue->toArray());
        self::assertCount(2, $spl, 'Source queue must not be consumed');
    }

    /**
     * @see  PriorityQueue::merge
     */
    #[Test]
    public function mergeCombinesQueuesByPriority(): void
    {
        $a = new PriorityQueue(['a'], 10);
        $b = new PriorityQueue(['b'], 20);
        $c = new PriorityQueue(['c'], 15);

        $a->merge($b, $c);

        self::assertSame(['b', 'c', 'a'], $a->toArray());
    }

    /**
     * @see  PriorityQueue::merge
     */
    #[Test]
    public function mergeRejectsNonQueueArguments(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('Only \SplPriorityQueue can merge.');

        (new PriorityQueue())->merge(['not a queue']);
    }

    /**
     * @see  PriorityQueue::toArray
     */
    #[Test]
    public function toArrayDoesNotConsumeQueue(): void
    {
        $queue = new PriorityQueue(['a', 'b']);

        $queue->toArray();

        self::assertCount(2, $queue);
    }

    /**
     * @see  PriorityQueue::__serialize
     * @see  PriorityQueue::__unserialize
     */
    #[Test]
    public function serializeRoundTripPreservesOrder(): void
    {
        $queue = new PriorityQueue();
        $queue->insertArray([1 => 'a', 3 => 'c', 2 => 'b']);

        $restored = unserialize(serialize($queue));

        self::assertSame(['c', 'b', 'a'], $restored->toArray());
    }

    /**
     * @see  PriorityQueue::compare
     */
    #[Test]
    public function compareChecksPriorityPartsInOrder(): void
    {
        $queue = new PriorityQueue();

        self::assertSame(0, $queue->compare([1, 2], [1, 2]));
        self::assertSame(1, $queue->compare([2, 0], [1, 9]));
        self::assertSame(-1, $queue->compare([1, 1], [1, 2]));
        self::assertSame(0, $queue->compare([1], [1, 2]));
    }
}
