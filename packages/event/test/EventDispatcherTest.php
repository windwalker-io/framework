<?php

declare(strict_types=1);

namespace Windwalker\Event\Test;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Event\Event;
use Windwalker\Event\EventDispatcher;
use Windwalker\Event\Provider\SimpleListenerProvider;
use Windwalker\Event\Test\Stub\StubFlowerEvent;

/**
 * The EventDispatcherTest class.
 */
class EventDispatcherTest extends TestCase
{
    /**
     * @var EventDispatcher
     */
    protected $instance;

    /**
     * @see  EventDispatcher::setListenerProvider
     */
    #[Test]
    public function setListenerProviderReplacesProvider(): void
    {
        $provider = new SimpleListenerProvider([]);

        self::assertSame($this->instance, $this->instance->setProvider($provider));
        self::assertSame($provider, $this->instance->getProvider());
    }

    /**
     * @see  EventDispatcher::__construct
     */
    #[Test]
    public function constructAcceptsListenerProvider(): void
    {
        $provider = new SimpleListenerProvider([]);

        self::assertSame($provider, new EventDispatcher($provider)->getProvider());
    }

    /**
     * @see  EventDispatcher::dispatch
     */
    #[Test]
    public function dispatchInvokesListenersAndReturnsSameEvent(): void
    {
        $value = null;

        $listener = function () use (&$value) {
            $value = 'Hello';
        };

        $d = new EventDispatcher(
            new SimpleListenerProvider(
                [
                    StubFlowerEvent::class => [$listener],
                ]
            )
        );

        $event2 = $d->dispatch($event = new StubFlowerEvent());

        self::assertSame($event2, $event);

        self::assertEquals('Hello', $value);
    }

    #[Test]
    public function dispatchSkipsListenersWhenEventAlreadyStopped(): void
    {
        $called = false;
        $d = new EventDispatcher(
            new SimpleListenerProvider(
                ['hello' => [static function () use (&$called) {
                    $called = true;
                }]]
            )
        );
        $event = new Event('hello');
        $event->stopPropagation();

        $d->dispatch($event);

        self::assertFalse($called);
    }

    #[Test]
    public function dispatchStopsAfterListenerStopsPropagation(): void
    {
        $calls = [];
        $d = new EventDispatcher(
            new SimpleListenerProvider(
                [
                    'hello' => [
                        static function (Event $event) use (&$calls) {
                            $calls[] = 1;
                            $event->stopPropagation();
                        },
                        static function () use (&$calls) {
                            $calls[] = 2;
                        },
                    ],
                ]
            )
        );

        $d->dispatch(new Event('hello'));

        self::assertSame([1], $calls);
    }

    #[Test]
    public function cloneDuplicatesProvider(): void
    {
        $clone = clone $this->instance;

        self::assertNotSame($this->instance->getProvider(), $clone->getProvider());
    }

    /**
     * @see  EventDispatcher::getListenerProvider
     */
    #[Test]
    public function getProviderReturnsInjectedProvider(): void
    {
        self::assertInstanceOf(SimpleListenerProvider::class, $this->instance->getProvider());
    }

    // /**
    //  * @see  EventDispatcher::emit
    //  */
    // public function testEmitWithString(): void
    // {
    //     $d = new EventDispatcher(new SimpleListenerProvider([
    //         'hello.event' => [
    //             static function (Event $event) {
    //                 $event->set('foo', $event['bar']);
    //             }
    //         ]
    //     ]));
    //
    //     $event2 = $d->emit('hello.event', ['bar' => 'YOO']);
    //
    //     self::assertEquals('YOO', $event2['foo']);
    // }

    #[Before]
    protected function createDispatcher(): void
    {
        $this->instance = new EventDispatcher(new SimpleListenerProvider([]));
    }
}
