<?php

declare(strict_types=1);

namespace Windwalker\Event\Test\Attributes;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Event\Attributes\EventSubscriber;
use Windwalker\Event\Attributes\ListenTo;
use Windwalker\Event\EventEmitter;
use Windwalker\Event\Listener\ListenerPriority;

/**
 * The ListenToTest class.
 */
class ListenToTest extends TestCase
{
    #[Test]
    public function defaultsToNormalPriorityAndNotOnce(): void
    {
        $listenTo = new ListenTo('foo');

        self::assertSame(ListenerPriority::NORMAL, $listenTo->priority);
        self::assertFalse($listenTo->once);
    }

    #[Test]
    public function listenRegistersListenerOnDispatcher(): void
    {
        $count = 0;
        $emitter = new EventEmitter();

        new ListenTo('foo')->listen(
            $emitter,
            static function () use (&$count) {
                $count++;
            }
        );

        $emitter->emit('foo');
        $emitter->emit('foo');

        self::assertSame(2, $count);
    }

    #[Test]
    public function listenWithOnceRunsListenerOnlyOnce(): void
    {
        $count = 0;
        $emitter = new EventEmitter();

        new ListenTo('foo', once: true)->listen(
            $emitter,
            static function () use (&$count) {
                $count++;
            }
        );

        $emitter->emit('foo');
        $emitter->emit('foo');

        self::assertSame(1, $count);
    }

    #[Test]
    public function listenRespectsPriority(): void
    {
        $order = [];
        $emitter = new EventEmitter();

        new ListenTo('foo', ListenerPriority::LOW)->listen(
            $emitter,
            static function () use (&$order) {
                $order[] = 'low';
            }
        );
        new ListenTo('foo', ListenerPriority::HIGH)->listen(
            $emitter,
            static function () use (&$order) {
                $order[] = 'high';
            }
        );

        $emitter->emit('foo');

        self::assertSame(['high', 'low'], $order);
    }

    #[Test]
    public function eventSubscriberDetectsAttributedClasses(): void
    {
        $subscriber = new #[EventSubscriber] class () {};
        $plain = new class () {};

        self::assertTrue(EventSubscriber::isSubscriber($subscriber));
        self::assertTrue(EventSubscriber::isSubscriber(new \ReflectionClass($subscriber)));
        self::assertFalse(EventSubscriber::isSubscriber($plain));
    }
}
