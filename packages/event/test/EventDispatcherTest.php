<?php

declare(strict_types=1);

namespace Windwalker\Event\Test;

use PHPUnit\Framework\TestCase;
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
    public function testSetListenerProvider(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  EventDispatcher::__construct
     */
    public function testConstruct(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  EventDispatcher::dispatch
     */
    public function testDispatch(): void
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

    /**
     * @see  EventDispatcher::getListenerProvider
     */
    public function testGetListenerProvider(): void
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

    protected function setUp(): void
    {
        $this->instance = new EventDispatcher(new SimpleListenerProvider([]));
    }

    protected function tearDown(): void
    {
    }
}
