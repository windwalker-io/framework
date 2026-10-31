<?php

declare(strict_types=1);

namespace Windwalker\Event\Test\Listener;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Event\Listener\ListenerCallable;
use Windwalker\Event\Listener\ListenerPriority;

/**
 * The ListenerCallableTest class.
 */
class ListenerCallableTest extends TestCase
{
    #[Test]
    public function nullPriorityFallsBackToNormal(): void
    {
        $listener = new ListenerCallable(static fn() => null, null, false);

        self::assertSame(ListenerPriority::NORMAL, $listener->getPriority());
        self::assertFalse($listener->isOnce());
    }

    #[Test]
    public function onceListenerRunsOnlyOnce(): void
    {
        $count = 0;
        $listener = new ListenerCallable(
            static function () use (&$count) {
                $count++;
            },
            ListenerPriority::HIGH,
            true
        );

        $listener();
        $listener();

        self::assertSame(1, $count);
        self::assertTrue($listener->isOnce());
        self::assertSame(ListenerPriority::HIGH, $listener->getPriority());
    }

    #[Test]
    public function sameWithComparesAgainstInnerCallable(): void
    {
        $fn = static fn() => null;
        $listener = new ListenerCallable($fn, null, false);

        self::assertTrue($listener->sameWith($fn));
        self::assertTrue($listener->sameWith(new ListenerCallable($fn, null, false)));
        self::assertFalse($listener->sameWith(static fn() => null));
    }
}
