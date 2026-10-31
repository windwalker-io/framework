<?php

declare(strict_types=1);

namespace Windwalker\Event\Test\Listener;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Event\Listener\ListenerPriority;
use Windwalker\Event\Listener\ListenersQueue;

use function Windwalker\disposable;

/**
 * The ListenersQueueTest class.
 */
class ListenersQueueTest extends TestCase
{
    #[Test]
    public function addAppendsListenersAndCountsThem(): void
    {
        $queue = new ListenersQueue();

        self::assertSame($queue, $queue->add($this->nope()));
        $queue->add($this->nope(), ListenerPriority::HIGH);

        self::assertCount(2, $queue);
    }

    #[Test]
    public function getAllReturnsListenersOrderedByPriority(): void
    {
        $low = $this->nope('low');
        $high = $this->nope('high');
        $normal = $this->nope('normal');

        $queue = new ListenersQueue();
        $queue->add($low, ListenerPriority::LOW);
        $queue->add($high, ListenerPriority::HIGH);
        $queue->add($normal);

        self::assertSame([$high, $normal, $low], $queue->getAll());
    }

    #[Test]
    public function hasFindsListenersByIdentity(): void
    {
        $fn = $this->nope();
        $queue = new ListenersQueue();
        $queue->add($fn);

        self::assertTrue($queue->has($fn));
        self::assertFalse($queue->has($this->nope()));
    }

    #[Test]
    public function hasAndRemoveMatchWrappedListenersByOriginalCallable(): void
    {
        $fn = $this->nope();
        $queue = new ListenersQueue();
        $queue->add(disposable($fn));

        self::assertTrue($queue->has($fn));

        $queue->remove($fn);

        self::assertCount(0, $queue);
    }

    #[Test]
    public function removeDropsOnlyMatchingListener(): void
    {
        $a = $this->nope('a');
        $b = $this->nope('b');
        $queue = new ListenersQueue();
        $queue->add($a)->add($b);

        self::assertSame($queue, $queue->remove($a));
        self::assertSame([$b], $queue->getAll());
    }

    private function nope(mixed $value = null): \Closure
    {
        return static fn() => $value;
    }
}
