<?php

declare(strict_types=1);

namespace Windwalker\Event\Test;

use Closure;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Event\Attributes\EventSubscriber;
use Windwalker\Event\Attributes\ListenTo;
use Windwalker\Event\Event;
use Windwalker\Event\EventDispatcher;
use Windwalker\Event\EventEmitter;
use Windwalker\Event\EventInterface;
use Windwalker\Event\Provider\SimpleListenerProvider;
use Windwalker\Event\Test\Stub\StubArgsEvent;
use Windwalker\Event\Test\Stub\StubFlowerEvent;
use Windwalker\Event\Test\Stub\StubTreeEvent;
use Windwalker\Utilities\TypeCast;

use function Windwalker\disposable;

/**
 * The EventEmitterTest class.
 */
class EventEmitterTest extends TestCase
{
    /**
     * @var EventEmitter
     */
    protected $instance;

    /**
     * @see  EventEmitter::getListeners
     */
    #[Test]
    public function getListenersReturnsQueueInRegistrationOrder(): void
    {
        $this->instance->on('hello', $fn1 = $this->nope(1));
        $this->instance->on('hello', $fn2 = $this->nope(2));

        $queue = array_values(TypeCast::toArray($this->instance->getListeners('hello')));

        self::assertSame(1, $queue[0]());
        self::assertSame(2, $queue[1]());
    }

    /**
     * @see  EventEmitter::once
     */
    #[Test]
    public function disposableListenerRunsOnlyOnce(): void
    {
        $count = 0;
        $fn = function () use (&$count) {
            $count++;
        };

        $this->instance->on('hello', disposable($fn));

        $this->instance->emit('hello');
        $this->instance->emit('hello');
        $this->instance->emit('hello');

        self::assertEquals(1, $count);
    }

    /**
     * @see  EventEmitter::once
     */
    #[Test]
    public function onceWithListenerCallableRunsOnlyOnce(): void
    {
        $count = 0;
        $fn = disposable(
            static function () use (&$count) {
                $count++;
            }
        );

        $this->instance->on('hello', $fn);

        $this->instance->emit('hello');
        $this->instance->emit('hello');
        $this->instance->emit('hello');

        self::assertEquals(1, $count);
    }

    /**
     * @see  EventEmitter::once
     */
    #[Test]
    public function onceWithDisposableCallableRunsOnlyOnce(): void
    {
        $count = 0;
        $fn = disposable(
            static function () use (&$count) {
                $count++;
            }
        );

        $this->instance->on('hello', $fn);

        $this->instance->emit('hello');
        $this->instance->emit('hello');
        $this->instance->emit('hello');

        self::assertEquals(1, $count);

        // Listener should auto remove
        self::assertCount(0, TypeCast::toArray($this->instance->getListeners('hello')));
    }

    /**
     * @see  EventEmitter::emit
     */
    #[Test]
    public function emitInvokesListenersWithEvent(): void
    {
        $count = 0;
        $fn = function (EventInterface $event) use (&$count) {
            $event['result'] = $count += $event['num'];
        };

        $this->instance->on('count', $fn);

        $event1 = $this->instance->emit('count', ['num' => 2]);
        $event2 = $this->instance->emit('count', ['num' => 5]);

        self::assertEquals(7, $count);
        self::assertEquals(2, $event1['result']);
        self::assertEquals(7, $event2['result']);
    }

    #[Test]
    public function subscribeRegistersListenToAttributesWithOnceSupport()
    {
        $subscriber = $this->getOnceSubscriber();

        $this->instance->subscribe($subscriber);

        $this->instance->emit('foo', ['num' => 2]);
        $this->instance->emit('foo', ['num' => 2]);
        $this->instance->emit('foo', ['num' => 2]);

        self::assertEquals(2, $subscriber->count);

        $this->instance->emit('bar', ['num' => 3]);
        $this->instance->emit('bar', ['num' => 4]);

        self::assertEquals(72, $subscriber->count);
    }

    #[Test]
    public function subscribeRegistersPublicMethodsAsListeners(): void
    {
        $subscriber = new class {
            public $count = 0;

            public function foo(EventInterface $event): void
            {
                $this->count += $event['num'];
            }

            public function bar(EventInterface $event): void
            {
                $this->count *= $event['num'];
            }
        };

        $this->instance->subscribe($subscriber);

        $this->instance->emit('foo', ['num' => 2]);
        $this->instance->emit('foo', ['num' => 2]);
        $this->instance->emit('foo', ['num' => 2]);

        self::assertEquals(6, $subscriber->count);

        $this->instance->emit('bar', ['num' => 3]);
        $this->instance->emit('bar', ['num' => 4]);

        self::assertEquals(72, $subscriber->count);
    }

    /**
     * @see  EventEmitter::off
     */
    #[Test]
    public function offRemovesListenerFromEvent(): void
    {
        $count = 0;
        $fn = function (EventInterface $event) use (&$count) {
            $event['result'] = $count += $event['num'];
        };

        $this->instance->on('count', $fn);

        $this->instance->emit('count', ['num' => 2]);
        $this->instance->emit('count', ['num' => 5]);

        $this->instance->off('count');

        $this->instance->emit('count', ['num' => 3]);
        $this->instance->emit('count', ['num' => 6]);

        self::assertEquals(7, $count);
    }

    /**
     * @see  EventEmitter::off
     */
    #[Test]
    public function offClosureRemovesOnlyThatListener(): void
    {
        $count = 0;
        $fn1 = function (EventInterface $event) use (&$count) {
            $event['result'] = $count += $event['num'];
        };
        $fn2 = function (EventInterface $event) use (&$count) {
            $event['result'] = $count += $event['num'];
        };

        $this->instance->on('count', $fn1);
        $this->instance->on('count', $fn2);

        $this->instance->off('count', $fn2);

        $this->instance->emit('count', ['num' => 2]);
        $this->instance->emit('count', ['num' => 5]);

        self::assertEquals(7, $count);
    }

    /**
     * @see  EventEmitter::off
     */
    #[Test]
    public function offCallableRemovesOnlyThatListener(): void
    {
        $subscriber = $this->getCounterSubscriber();

        $this->instance->subscribe($subscriber);

        $this->instance->off('count', [$subscriber, 'count1']);

        $this->instance->emit('count', ['num' => 2]);
        $this->instance->emit('count', ['num' => 5]);
        $this->instance->emit('flower', ['num' => 10]);

        self::assertEquals(7, $subscriber->count);
        self::assertEquals('Sakura', $subscriber->flower);
    }

    /**
     * @see  EventEmitter::off
     */
    #[Test]
    public function offSubscriberRemovesAllItsListeners(): void
    {
        $subscriber = $this->getCounterSubscriber();

        $this->instance->subscribe($subscriber);

        $this->instance->off('count', $subscriber);

        $this->instance->emit('count', ['num' => 2]);
        $this->instance->emit('count', ['num' => 5]);

        self::assertEquals(0, $subscriber->count);
    }

    /**
     * @see  EventEmitter::off
     */
    #[Test]
    public function offSubscriberRemovesListenersRegisteredAsClosures(): void
    {
        $subscriber = $this->getCounterSubscriber();

        $this->instance->on('count', Closure::fromCallable([$subscriber, 'count1']));
        $this->instance->on('count', Closure::fromCallable([$subscriber, 'count2']));

        $this->instance->off('count', $subscriber);

        $this->instance->emit('count', ['num' => 2]);
        $this->instance->emit('count', ['num' => 5]);

        self::assertEquals(0, $subscriber->count);
    }

    /**
     * @see  EventEmitter::on
     */
    #[Test]
    public function onRegistersListenersByPriority(): void
    {
        $values = [];

        $this->instance->on(
            'foo',
            function () use (&$values) {
                $values[] = 500;
            },
            500
        );

        $this->instance->on(
            'foo',
            function () use (&$values) {
                $values[] = 300;
            },
            300
        );

        $this->instance->on(
            'foo',
            function () use (&$values) {
                $values[] = 700;
            },
            700
        );

        $this->instance->emit('foo');

        self::assertEquals([700, 500, 300], $values);
    }

    /**
     * @see  EventEmitter::__construct
     */
    #[Test]
    public function constructAcceptsListenerProviderAndDispatcher(): void
    {
        $fn = static fn() => null;
        $emitter = new EventEmitter(new SimpleListenerProvider(['foo' => [$fn]]));

        self::assertSame([$fn], iterator_to_array($emitter->getListeners('foo'), false));
    }

    #[Test]
    public function emitWithPlainClassStringCreatesObjectWithConstructorArguments(): void
    {
        $emitter = new EventEmitter();
        $received = null;
        $emitter->on(
            StubArgsEvent::class,
            static function (StubArgsEvent $event) use (&$received) {
                $received = $event->value;
            }
        );

        $event = $emitter->emit(StubArgsEvent::class, ['value' => 'hello']);

        self::assertInstanceOf(StubArgsEvent::class, $event);
        self::assertSame('hello', $received);
    }

    #[Test]
    public function emitWithAbstractEventClassMergesArguments(): void
    {
        $emitter = new EventEmitter();

        $event = $emitter->emit(StubTreeEvent::class, ['tree' => 'Oak']);

        self::assertInstanceOf(StubTreeEvent::class, $event);
        self::assertSame('Oak', $event['tree']);
    }

    #[Test]
    public function emitWithEventObjectMergesArgumentsAndKeepsInstance(): void
    {
        $emitter = new EventEmitter();
        $event = new Event('foo', ['a' => 1]);

        self::assertSame($event, $emitter->emit($event, ['b' => 2]));
        self::assertSame(['a' => 1, 'b' => 2], $event->getArguments());
    }

    #[Test]
    public function emitWithPlainObjectDispatchesWithoutModification(): void
    {
        $emitter = new EventEmitter();
        $received = null;
        $emitter->appendProvider(
            new SimpleListenerProvider(
                [StubFlowerEvent::class => [static function ($event) use (&$received) {
                    $received = $event;
                }]]
            )
        );
        $object = new StubFlowerEvent();

        self::assertSame($object, $emitter->emit($object));
        self::assertSame($object, $received);
    }

    #[Test]
    public function dispatchForwardsEventToDealers(): void
    {
        $emitter = new EventEmitter();
        $dealer = new EventEmitter();
        $count = 0;
        $dealer->on(
            'foo',
            static function () use (&$count) {
                $count++;
            }
        );

        self::assertSame($emitter, $emitter->addDealer($dealer));
        $emitter->emit('foo');

        self::assertSame(1, $count);
        self::assertCount(1, $emitter->getDealers());
    }

    #[Test]
    public function resetDealersStopsForwarding(): void
    {
        $emitter = new EventEmitter();
        $dealer = new EventEmitter();
        $count = 0;
        $dealer->on(
            'foo',
            static function () use (&$count) {
                $count++;
            }
        );
        $emitter->addDealer($dealer);

        self::assertSame($emitter, $emitter->resetDealers());
        $emitter->emit('foo');

        self::assertSame(0, $count);
    }

    #[Test]
    public function cloneKeepsDealersAndListenersIndependent(): void
    {
        $emitter = new EventEmitter();
        $dealer = new EventEmitter();
        $emitter->addDealer($dealer);
        $emitter->on('foo', static fn() => null);

        $clone = clone $emitter;
        $clone->resetDealers();
        $clone->off('foo');

        self::assertCount(1, $emitter->getDealers());
        self::assertCount(1, iterator_to_array($emitter->getListeners('foo'), false));
        self::assertCount(0, iterator_to_array($clone->getListeners('foo'), false));
    }

    #[Test]
    public function onceMethodRegistersListenerThatRunsOnce(): void
    {
        $emitter = new EventEmitter();
        $count = 0;

        self::assertSame(
            $emitter,
            $emitter->once(
                'foo',
                static function () use (&$count) {
                    $count++;
                }
            )
        );
        $emitter->emit('foo');
        $emitter->emit('foo');

        self::assertSame(1, $count);
    }

    /**
     * @see  EventEmitter::remove
     */
    #[Test]
    public function removeCallableRemovesListenerFromAllEvents(): void
    {
        $subscriber = $this->getCounterSubscriber();

        $this->instance->on('flower', [$subscriber, 'count1']);

        $this->instance->subscribe($subscriber);

        $this->instance->remove([$subscriber, 'count1']);

        $this->instance->emit('count', ['num' => 2]);
        $this->instance->emit('count', ['num' => 5]);
        $this->instance->emit('flower', ['num' => 5]);

        self::assertEquals(7, $subscriber->count);
        self::assertEquals('Sakura', $subscriber->flower);
    }

    /**
     * @see  EventEmitter::remove
     */
    #[Test]
    public function removeSubscriberRemovesListenersFromAllEvents(): void
    {
        $subscriber = $this->getCounterSubscriber();

        $this->instance->on('flower', [$subscriber, 'count1']);

        $this->instance->subscribe($subscriber);

        $this->instance->remove($subscriber);

        $this->instance->emit('count', ['num' => 2]);
        $this->instance->emit('count', ['num' => 5]);
        $this->instance->emit('flower', ['num' => 5]);

        self::assertEquals(0, $subscriber->count);
        self::assertEquals('', $subscriber->flower);
    }

    protected function getCounterSubscriber(): object
    {
        // phpcs:disable
        return new
        #[EventSubscriber]
        // phpcs:enable
        class {
            public $count = 0;

            public $flower = '';

            #[ListenTo('count')]
            public function count1(
                EventInterface $event
            ): void {
                $event['result'] = $this->count += $event['num'];
            }

            #[ListenTo('count')]
            public function count2(
                EventInterface $event
            ): void {
                $event['result'] = $this->count += $event['num'];
            }

            #[ListenTo('flower')]
            public function sakura()
            {
                $this->flower = 'Sakura';
            }
        };
    }

    protected function getOnceSubscriber(): object
    {
        // phpcs:disable
        return new
        #[EventSubscriber]
        // phpcs:enable
        class {
            public $count = 0;

            #[ListenTo('foo', 500, true)]
            public function foo(
                EventInterface $event
            ) {
                $this->count += $event['num'];
            }

            #[ListenTo('bar', 100, true)]
            public function bar1(
                EventInterface $event
            ) {
                $this->count *= $event['num'];
            }

            #[ListenTo('bar', 100)]
            public function bar2(
                EventInterface $event
            ) {
                $this->count *= $event['num'];
            }
        };
    }

    #[Test]
    public function observeEmitsEventsToObservers(): void
    {
        $values = [];

        $this->instance->observe('hello')
            ->map(
                static function (EventInterface $event) {
                    $event['num'] += 50;

                    return $event;
                }
            )
            ->map(
                static function (EventInterface $event) {
                    return $event['num'];
                }
            )
            ->subscribe(
                static function ($v) use (&$values) {
                    $values[] = $v;
                }
            );

        $this->instance->emit('hello', ['num' => 3]);
        $this->instance->emit('hello', ['num' => 4]);
        $this->instance->emit('hello', ['num' => 5]);

        self::assertEquals([53, 54, 55], $values);
    }

    #[Test]
    public function appendProviderRegistersListenersFromProvider(): void
    {
        $this->instance->on(
            'hello',
            function (EventInterface $event) {
                $event['main'] = true;
            }
        );

        $provider1 = new SimpleListenerProvider(
            [
                'hello' => [
                    function (EventInterface $event) {
                        $event['sub1'] = true;
                    },
                ],
            ]
        );

        $provider2 = new SimpleListenerProvider(
            [
                'hello' => [
                    function (EventInterface $event) {
                        $event['sub2'] = true;
                    },
                ],
            ]
        );

        $this->instance->appendProvider($provider1)
            ->appendProvider($provider2);

        $event = $this->instance->emit('hello');

        self::assertEquals(['main', 'sub1', 'sub2'], array_keys($event->getArguments()));
    }

    #[Test]
    public function registerDealerForwardsEventsToDealer(): void
    {
        $this->instance->on(
            'hello',
            function (EventInterface $event) {
                $event['main'] = true;
            }
        );

        $dealer1 = new EventDispatcher(
            new SimpleListenerProvider(
                [
                    'hello' => [
                        function (EventInterface $event) {
                            $event['sub1'] = true;
                        },
                    ],
                ]
            )
        );

        $dealer2 = new EventDispatcher(
            new SimpleListenerProvider(
                [
                    'hello' => [
                        function (EventInterface $event) {
                            $event['sub2'] = true;
                        },
                    ],
                ]
            )
        );

        $this->instance->addDealer($dealer1)
            ->addDealer($dealer2);

        $event = $this->instance->emit('hello');

        self::assertEquals(['main', 'sub1', 'sub2'], array_keys($event->getArguments()));
    }

    #[Before]
    protected function createEmitter(): void
    {
        $this->instance = new EventEmitter();
    }

    protected function nope($value = null): Closure
    {
        return function () use ($value) {
            return $value;
        };
    }
}
