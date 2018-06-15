<?php
/**
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT Source Matters. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later..txt
 */

namespace Windwalker\Event\Test;

use Windwalker\Event\Dispatcher;
use Windwalker\Event\Event;
use Windwalker\Event\EventImmutable;
use Windwalker\Event\EventInterface;
use Windwalker\Event\ListenerPriority;
use Windwalker\Event\Test\Stub\EmptyListener;
use Windwalker\Event\Test\Stub\FirstListener;
use Windwalker\Event\Test\Stub\SecondListener;
use Windwalker\Event\Test\Stub\SomethingListener;
use Windwalker\Event\Test\Stub\ThirdListener;

/**
 * Tests for the Dispatcher class.
 *
 * @since  2.0
 */
class DispatcherTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Object under tests.
     *
     * @var    Dispatcher
     *
     * @since  2.0
     */
    private $instance;

    /**
     * Test the setEvent method.
     *
     * @return  void
     *
     * @covers  \Windwalker\Event\Dispatcher::setEvent
     * @since   2.0
     */
    public function testSetEvent()
    {
        $event = new Event('onTest');
        $this->instance->setEvent($event);
        $this->assertTrue($this->instance->hasEvent('onTest'));
        $this->assertSame($event, $this->instance->getEvent('onTest'));

//		$immutableEvent = new EventImmutable('onAfterSomething');
//		$this->instance->setEvent($immutableEvent);
//		$this->assertTrue($this->instance->hasEvent('onAfterSomething'));
//		$this->assertSame($immutableEvent, $this->instance->getEvent('onAfterSomething'));

        // Setting an existing event will replace the old one.
        $eventCopy = new Event('onTest');
        $this->instance->setEvent($eventCopy);
        $this->assertTrue($this->instance->hasEvent('onTest'));
        $this->assertSame($eventCopy, $this->instance->getEvent('onTest'));
    }

    /**
     * Test the addEvent method.
     *
     * @return  void
     *
     * @covers  \Windwalker\Event\Dispatcher::addEvent
     * @since   2.0
     */
    public function testAddEvent()
    {
        $event = new Event('onTest');
        $this->instance->addEvent($event);
        $this->assertTrue($this->instance->hasEvent('onTest'));
        $this->assertSame($event, $this->instance->getEvent('onTest'));

//		$immutableEvent = new EventImmutable('onAfterSomething');
//		$this->instance->addEvent($immutableEvent);
//		$this->assertTrue($this->instance->hasEvent('onAfterSomething'));
//		$this->assertSame($immutableEvent, $this->instance->getEvent('onAfterSomething'));

        // Adding an existing event will have no effect.
        $eventCopy = new Event('onTest');
        $this->instance->addEvent($eventCopy);
        $this->assertTrue($this->instance->hasEvent('onTest'));
        $this->assertSame($event, $this->instance->getEvent('onTest'));
    }

    /**
     * Test the hasEvent method.
     *
     * @return  void
     *
     * @covers  \Windwalker\Event\Dispatcher::hasEvent
     * @since   2.0
     */
    public function testHasEvent()
    {
        $this->assertFalse($this->instance->hasEvent('onTest'));

        $event = new Event('onTest');
        $this->instance->addEvent($event);
        $this->assertTrue($this->instance->hasEvent($event));
    }

    /**
     * Test the getEvent method when the event doesn't exist.
     *
     * @return  void
     *
     * @covers  \Windwalker\Event\Dispatcher::getEvent
     * @since   2.0
     */
    public function testGetEventNonExisting()
    {
        $this->assertNull($this->instance->getEvent('non-existing'));
        $this->assertFalse($this->instance->getEvent('non-existing', false));
    }

    /**
     * Test the removeEvent method.
     *
     * @return  void
     *
     * @covers  \Windwalker\Event\Dispatcher::removeEvent
     * @since   2.0
     */
    public function testRemoveEvent()
    {
        // No exception.
        $this->instance->removeEvent('non-existing');

        $event = new Event('onTest');
        $this->instance->addEvent($event);

        // Remove by passing the instance.
        $this->instance->removeEvent($event);
        $this->assertFalse($this->instance->hasEvent('onTest'));

        $this->instance->addEvent($event);

        // Remove by name.
        $this->instance->removeEvent('onTest');
        $this->assertFalse($this->instance->hasEvent('onTest'));
    }

    /**
     * Test the getEvents method.
     *
     * @return  void
     *
     * @covers  \Windwalker\Event\Dispatcher::getEvents
     * @since   2.0
     */
    public function testGetEvents()
    {
        $this->assertEmpty($this->instance->getEvents());

        $event1 = new Event('onBeforeTest');
        $event2 = new Event('onTest');
        $event3 = new Event('onAfterTest');

        $this->instance->addEvent($event1)
            ->addEvent($event2)
            ->addEvent($event3);

        $expected = [
            'onBeforeTest' => $event1,
            'onTest' => $event2,
            'onAfterTest' => $event3,
        ];

        $this->assertSame($expected, $this->instance->getEvents());
    }

    /**
     * Test the clearEvents method.
     *
     * @return  void
     *
     * @covers  \Windwalker\Event\Dispatcher::clearEvents
     * @since   2.0
     */
    public function testClearEvents()
    {
        $event1 = new Event('onBeforeTest');
        $event2 = new Event('onTest');
        $event3 = new Event('onAfterTest');

        $this->instance->addEvent($event1)
            ->addEvent($event2)
            ->addEvent($event3);

        $this->instance->clearEvents();

        $this->assertFalse($this->instance->hasEvent('onBeforeTest'));
        $this->assertFalse($this->instance->hasEvent('onTest'));
        $this->assertFalse($this->instance->hasEvent('onAfterTest'));
        $this->assertEmpty($this->instance->getEvents());
    }

    /**
     * Test the countEvents method.
     *
     * @return  void
     *
     * @covers  \Windwalker\Event\Dispatcher::countEvents
     * @since   2.0
     */
    public function testCountEvents()
    {
        $this->assertEquals(0, $this->instance->countEvents());

        $event1 = new Event('onBeforeTest');
        $event2 = new Event('onTest');
        $event3 = new Event('onAfterTest');

        $this->instance->addEvent($event1)
            ->addEvent($event2)
            ->addEvent($event3);

        $this->assertEquals(3, $this->instance->countEvents());
    }

    /**
     * Test the addListener method with an empty listener (no methods).
     * It shouldn't be registered to any event.
     *
     * @return  void
     *
     * @covers  \Windwalker\Event\Dispatcher::addListener
     * @since   2.0
     */
    public function testAddListenerEmpty()
    {
        $listener = new EmptyListener();
        $this->instance->addListener($listener);

        $this->assertFalse($this->instance->hasListener($listener));

        $this->instance->addListener($listener, ['onSomething']);
        $this->assertFalse($this->instance->hasListener($listener, 'onSomething'));
    }

    /**
     * Test the addListener method.
     *
     * @return  void
     *
     * @covers  \Windwalker\Event\Dispatcher::addListener
     * @since   2.0
     */
    public function testAddListener()
    {
        // Add 3 listeners listening to the same events.
        $listener1 = new SomethingListener();
        $listener2 = new SomethingListener();
        $listener3 = new SomethingListener();

        $this->instance->addListener($listener1)
            ->addListener($listener2)
            ->addListener($listener3);

        $this->assertTrue($this->instance->hasListener($listener1));
        $this->assertTrue($this->instance->hasListener($listener1, 'onBeforeSomething'));
        $this->assertTrue($this->instance->hasListener($listener1, 'onSomething'));
        $this->assertTrue($this->instance->hasListener($listener1, 'onAfterSomething'));

        $this->assertTrue($this->instance->hasListener($listener2));
        $this->assertTrue($this->instance->hasListener($listener2, 'onBeforeSomething'));
        $this->assertTrue($this->instance->hasListener($listener2, 'onSomething'));
        $this->assertTrue($this->instance->hasListener($listener2, 'onAfterSomething'));

        $this->assertTrue($this->instance->hasListener($listener3));
        $this->assertTrue($this->instance->hasListener($listener3, 'onBeforeSomething'));
        $this->assertTrue($this->instance->hasListener($listener3, 'onSomething'));
        $this->assertTrue($this->instance->hasListener($listener3, 'onAfterSomething'));

        $this->assertEquals(
            ListenerPriority::NORMAL,
            $this->instance->getListenerPriority($listener1, 'onBeforeSomething')
        );
        $this->assertEquals(ListenerPriority::NORMAL, $this->instance->getListenerPriority($listener1, 'onSomething'));
        $this->assertEquals(
            ListenerPriority::NORMAL,
            $this->instance->getListenerPriority($listener1, 'onAfterSomething')
        );

        $this->assertEquals(
            ListenerPriority::NORMAL,
            $this->instance->getListenerPriority($listener1, 'onBeforeSomething')
        );
        $this->assertEquals(ListenerPriority::NORMAL, $this->instance->getListenerPriority($listener1, 'onSomething'));
        $this->assertEquals(
            ListenerPriority::NORMAL,
            $this->instance->getListenerPriority($listener1, 'onAfterSomething')
        );

        $this->assertEquals(
            ListenerPriority::NORMAL,
            $this->instance->getListenerPriority($listener3, 'onBeforeSomething')
        );
        $this->assertEquals(ListenerPriority::NORMAL, $this->instance->getListenerPriority($listener3, 'onSomething'));
        $this->assertEquals(
            ListenerPriority::NORMAL,
            $this->instance->getListenerPriority($listener3, 'onAfterSomething')
        );
    }

    /**
     * Test the addListener method by specifying the events and priorities.
     *
     * @return  void
     *
     * @covers  \Windwalker\Event\Dispatcher::addListener
     * @since   2.0
     */
    public function testAddListenerSpecifiedPriorities()
    {
        $listener = new SomethingListener();

        $this->instance->addListener(
            $listener,
            [
                'onBeforeSomething' => ListenerPriority::MIN,
                'onSomething' => ListenerPriority::ABOVE_NORMAL,
                'onAfterSomething' => ListenerPriority::HIGH,
            ]
        );

        $this->assertTrue($this->instance->hasListener($listener, 'onBeforeSomething'));
        $this->assertTrue($this->instance->hasListener($listener, 'onSomething'));
        $this->assertTrue($this->instance->hasListener($listener, 'onAfterSomething'));

        $this->assertEquals(
            ListenerPriority::MIN,
            $this->instance->getListenerPriority($listener, 'onBeforeSomething')
        );
        $this->assertEquals(
            ListenerPriority::ABOVE_NORMAL,
            $this->instance->getListenerPriority($listener, 'onSomething')
        );
        $this->assertEquals(
            ListenerPriority::HIGH,
            $this->instance->getListenerPriority($listener, 'onAfterSomething')
        );
    }

    /**
     * Test the addListener method by specifying the events and priorities.
     *
     * @return  void
     *
     * @covers  \Windwalker\Event\Dispatcher::listen
     * @since   2.0
     */
    public function testAddSingleListener()
    {
        $listener = function (Event $event) {
        };

        $this->instance->listen('onBeforeSomething', $listener, ListenerPriority::MIN);

        $this->assertEquals(
            ListenerPriority::MIN,
            $this->instance->getListenerPriority($listener, 'onBeforeSomething')
        );
    }

    /**
     * Test the addListener method by specifying less events than its methods.
     *
     * @return  void
     *
     * @covers  \Windwalker\Event\Dispatcher::addListener
     * @since   2.0
     */
    public function testAddListenerLessEvents()
    {
        $listener = new SomethingListener();

        $this->instance->addListener(
            $listener,
            [
                'onBeforeSomething' => ListenerPriority::NORMAL,
                'onAfterSomething' => ListenerPriority::HIGH,
            ]
        );

        $this->assertFalse($this->instance->hasListener($listener, 'onSomething'));
        $this->assertTrue($this->instance->hasListener($listener, 'onBeforeSomething'));
        $this->assertTrue($this->instance->hasListener($listener, 'onAfterSomething'));
    }

    /**
     * Test the addListener method with a closure listener.
     *
     * @return  void
     *
     * @covers  \Windwalker\Event\Dispatcher::addListener
     * @since   2.0
     */
    public function testAddClosureListener()
    {
        $listener = function (EventInterface $event) {
        };

        $this->instance->addListener(
            $listener,
            [
                'onSomething' => ListenerPriority::HIGH,
                'onAfterSomething' => ListenerPriority::NORMAL,
            ]
        );

        $this->assertTrue($this->instance->hasListener($listener));
        $this->assertTrue($this->instance->hasListener($listener, 'onSomething'));
        $this->assertTrue($this->instance->hasListener($listener, 'onAfterSomething'));

        $this->assertEquals(ListenerPriority::HIGH, $this->instance->getListenerPriority($listener, 'onSomething'));
        $this->assertEquals(
            ListenerPriority::NORMAL,
            $this->instance->getListenerPriority($listener, 'onAfterSomething')
        );
    }

    /**
     * Test the addListener method with a closure listener without specified event.
     *
     * @return  void
     *
     * @covers             Windwalker\Event\Dispatcher::addListener
     * @expectedException  \InvalidArgumentException
     * @since              2.0
     */
    public function testAddClosureListenerNoEventsException()
    {
        $this->instance->addListener(
            function (EventInterface $event) {
            }
        );
    }

    /**
     * Test the addListener method with an invalid listener.
     *
     * @expectedException  \InvalidArgumentException
     *
     * @return  void
     *
     * @covers  \Windwalker\Event\Dispatcher::addListener
     * @since   2.0
     */
    public function testAddListenerInvalidListenerException()
    {
        $this->instance->addListener('trim');
    }

    /**
     * Test the getListenerPriority method.
     *
     * @return  void
     *
     * @covers  \Windwalker\Event\Dispatcher::getListenerPriority
     * @since   2.0
     */
    public function testGetListenerPriority()
    {
        $this->assertNull($this->instance->getListenerPriority(new \stdClass(), 'onTest'));

        $listener = new SomethingListener();
        $this->instance->addListener($listener);

        $this->assertEquals(
            ListenerPriority::NORMAL,
            $this->instance->getListenerPriority(
                $listener,
                new Event('onSomething')
            )
        );
    }

    /**
     * Test the getListeners method.
     *
     * @return  void
     *
     * @covers  \Windwalker\Event\Dispatcher::getListeners
     * @since   2.0
     */
    public function testGetListeners()
    {
        $this->assertEmpty($this->instance->getListeners('onSomething'));

        $listener1 = new SomethingListener();
        $listener2 = new SomethingListener();
        $listener3 = new SomethingListener();

        $this->instance->addListener($listener1)
            ->addListener($listener2)
            ->addListener($listener3);

        $onBeforeSomethingListeners = $this->instance->getListeners('onBeforeSomething');

        $this->assertSame($listener1, $onBeforeSomethingListeners[0]);
        $this->assertSame($listener2, $onBeforeSomethingListeners[1]);
        $this->assertSame($listener3, $onBeforeSomethingListeners[2]);

        $onSomethingListeners = $this->instance->getListeners(new Event('onSomething'));

        $this->assertSame($listener1, $onSomethingListeners[0]);
        $this->assertSame($listener2, $onSomethingListeners[1]);
        $this->assertSame($listener3, $onSomethingListeners[2]);

        $onAfterSomethingListeners = $this->instance->getListeners('onAfterSomething');

        $this->assertSame($listener1, $onAfterSomethingListeners[0]);
        $this->assertSame($listener2, $onAfterSomethingListeners[1]);
        $this->assertSame($listener3, $onAfterSomethingListeners[2]);
    }

    /**
     * Test the hasListener method.
     *
     * @return  void
     *
     * @covers  \Windwalker\Event\Dispatcher::hasListener
     * @since   2.0
     */
    public function testHasListener()
    {
        $this->assertFalse($this->instance->hasListener(new \stdClass(), 'onTest'));

        $listener = new SomethingListener();
        $this->instance->addListener($listener);
        $this->assertTrue($this->instance->hasListener($listener, new Event('onSomething')));
    }

    /**
     * Test the removeListener method.
     *
     * @return  void
     *
     * @covers  \Windwalker\Event\Dispatcher::removeListener
     * @since   2.0
     */
    public function testRemoveListeners()
    {
        $listener = new SomethingListener();
        $this->instance->addListener($listener);

        // Remove the listener from all events.
        $this->instance->removeListener($listener);

        $this->assertFalse($this->instance->hasListener($listener, 'onBeforeSomething'));
        $this->assertFalse($this->instance->hasListener($listener, 'onSomething'));
        $this->assertFalse($this->instance->hasListener($listener, 'onAfterSomething'));

        $this->instance->addListener($listener);

        // Remove the listener from a specific event.
        $this->instance->removeListener($listener, 'onBeforeSomething');

        $this->assertFalse($this->instance->hasListener($listener, 'onBeforeSomething'));
        $this->assertTrue($this->instance->hasListener($listener, 'onSomething'));
        $this->assertTrue($this->instance->hasListener($listener, 'onAfterSomething'));

        // Remove the listener from a specific event by passing an event object.
        $this->instance->removeListener($listener, new Event('onSomething'));

        $this->assertFalse($this->instance->hasListener($listener, 'onSomething'));
        $this->assertTrue($this->instance->hasListener($listener, 'onAfterSomething'));
    }

    /**
     * Test the clearListeners method.
     *
     * @return  void
     *
     * @covers  \Windwalker\Event\Dispatcher::clearListeners
     * @since   2.0
     */
    public function testClearListeners()
    {
        $listener1 = new SomethingListener();
        $listener2 = new SomethingListener();
        $listener3 = new SomethingListener();

        $this->instance->addListener($listener1)
            ->addListener($listener2)
            ->addListener($listener3);

        // Test without specified event.
        $this->instance->clearListeners();

        $this->assertFalse($this->instance->hasListener($listener1));
        $this->assertFalse($this->instance->hasListener($listener2));
        $this->assertFalse($this->instance->hasListener($listener3));

        // Test with an event specified.
        $this->instance->addListener($listener1)
            ->addListener($listener2)
            ->addListener($listener3);

        $this->instance->clearListeners('onSomething');

        $this->assertTrue($this->instance->hasListener($listener1));
        $this->assertTrue($this->instance->hasListener($listener2));
        $this->assertTrue($this->instance->hasListener($listener3));

        $this->assertFalse($this->instance->hasListener($listener1, 'onSomething'));
        $this->assertFalse($this->instance->hasListener($listener2, 'onSomething'));
        $this->assertFalse($this->instance->hasListener($listener3, 'onSomething'));

        // Test with a specified event object.
        $this->instance->clearListeners(new Event('onAfterSomething'));

        $this->assertTrue($this->instance->hasListener($listener1));
        $this->assertTrue($this->instance->hasListener($listener2));
        $this->assertTrue($this->instance->hasListener($listener3));

        $this->assertFalse($this->instance->hasListener($listener1, 'onAfterSomething'));
        $this->assertFalse($this->instance->hasListener($listener2, 'onAfterSomething'));
        $this->assertFalse($this->instance->hasListener($listener3, 'onAfterSomething'));
    }

    /**
     * Test the clearListeners method.
     *
     * @return  void
     *
     * @covers  \Windwalker\Event\Dispatcher::clearListeners
     * @since   2.0
     */
    public function testCountListeners()
    {
        $this->assertEquals(0, $this->instance->countListeners('onTest'));

        $listener1 = new SomethingListener();
        $listener2 = new SomethingListener();
        $listener3 = new SomethingListener();

        $this->instance->addListener($listener1)
            ->addListener($listener2)
            ->addListener($listener3);

        $this->assertEquals(3, $this->instance->countListeners('onSomething'));
        $this->assertEquals(3, $this->instance->countListeners(new Event('onSomething')));
    }

    /**
     * Test the triggerEvent method with no listeners listening to the event.
     *
     * @return  void
     *
     * @covers  \Windwalker\Event\Dispatcher::triggerEvent
     * @since   2.0
     */
    public function testTriggerEventNoListeners()
    {
        $this->assertInstanceOf('Windwalker\Event\Event', $this->instance->triggerEvent('onTest'));

        $event = new Event('onTest');
        $this->assertSame($event, $this->instance->triggerEvent($event));
    }

    /**
     * Test the trigger event method with listeners having the same priority.
     * We expect the listener to be called in the order they were added.
     *
     * @return  void
     *
     * @covers  \Windwalker\Event\Dispatcher::triggerEvent
     * @since   2.0
     */
    public function testTriggerEventSamePriority()
    {
        $first  = new FirstListener();
        $second = new SecondListener();
        $third  = new ThirdListener();

        $fourth = function (Event $event) {
            $listeners   = $event->getArgument('listeners');
            $listeners[] = 'fourth';
            $event->setArgument('listeners', $listeners);
        };

        $fifth = function (Event $event) {
            $listeners   = $event->getArgument('listeners');
            $listeners[] = 'fifth';
            $event->setArgument('listeners', $listeners);
        };

        $this->instance->addListener($first)
            ->addListener($second)
            ->addListener($third)
            ->addListener($fourth, ['onSomething' => ListenerPriority::NORMAL])
            ->addListener($fifth, ['onSomething' => ListenerPriority::NORMAL]);

        // Inspect the event arguments to know the order of the listeners.
        /** @var $event Event */
        $event = $this->instance->triggerEvent('onSomething');

        $listeners = $event->getArgument('listeners');

        $this->assertEquals(
            $listeners,
            ['first', 'second', 'third', 'fourth', 'fifth']
        );
    }

    /**
     * Test the trigger event method with listeners having different priorities.
     *
     * @return  void
     *
     * @covers  \Windwalker\Event\Dispatcher::triggerEvent
     * @since   2.0
     */
    public function testTriggerEventDifferentPriorities()
    {
        $first  = new FirstListener();
        $second = new SecondListener();
        $third  = new ThirdListener();

        $fourth = function (Event $event) {
            $listeners   = $event->getArgument('listeners');
            $listeners[] = 'fourth';
            $event->setArgument('listeners', $listeners);
        };

        $fifth = function (Event $event) {
            $listeners   = $event->getArgument('listeners');
            $listeners[] = 'fifth';
            $event->setArgument('listeners', $listeners);
        };

        $this->instance->addListener($fourth, ['onSomething' => ListenerPriority::BELOW_NORMAL]);
        $this->instance->addListener($fifth, ['onSomething' => ListenerPriority::BELOW_NORMAL]);
        $this->instance->addListener($first, ['onSomething' => ListenerPriority::HIGH]);
        $this->instance->addListener($second, ['onSomething' => ListenerPriority::HIGH]);
        $this->instance->addListener($third, ['onSomething' => ListenerPriority::ABOVE_NORMAL]);

        // Inspect the event arguments to know the order of the listeners.
        /** @var $event Event */
        $event = $this->instance->triggerEvent('onSomething');

        $listeners = $event->getArgument('listeners');

        $this->assertEquals(
            $listeners,
            ['first', 'second', 'third', 'fourth', 'fifth']
        );
    }

    /**
     * Test the trigger event method with a listener stopping the event propagation.
     *
     * @return  void
     *
     * @covers  \Windwalker\Event\Dispatcher::triggerEvent
     * @since   2.0
     */
    public function testTriggerEventStopped()
    {
        $first  = new FirstListener();
        $second = new SecondListener();
        $third  = new ThirdListener();

        $stopper = function (Event $event) {
            $event->stop();
        };

        $this->instance->addListener($first)
            ->addListener($second)
            ->addListener($stopper, ['onSomething' => ListenerPriority::NORMAL])
            ->addListener($third);

        /** @var $event Event */
        $event = $this->instance->triggerEvent('onSomething');

        $listeners = $event->getArgument('listeners');

        // The third listener was not called because the stopper stopped the event.
        $this->assertEquals(
            $listeners,
            ['first', 'second']
        );
    }

    /**
     * Test the triggerEvent method with a previously registered event.
     *
     * @return  void
     *
     * @covers  \Windwalker\Event\Dispatcher::triggerEvent
     * @since   2.0
     */
    public function testTriggerEventRegistered()
    {
        // TODO: Use new mock builder
        $this->markTestSkipped('Rewrite to use new mock builder');

        $event = new Event('onSomething');

        $mockedListener = $this->getMock('Windwalker\Event\Test\Stub\SomethingListener', ['onSomething']);
        $mockedListener->expects($this->once())
            ->method('onSomething')
            ->with($event);

        $this->instance->addEvent($event);
        $this->instance->addListener($mockedListener);

        $this->instance->triggerEvent('onSomething');
    }

    /**
     * testTriggerEventReference
     *
     * @return  void
     */
    public function testTriggerEventReference()
    {
        $event = new Event('onSomething');

        $foo  = 'foo';
        $args = ['foo' => &$foo];

        $this->instance->triggerEvent($event, $args);

        $event->setArgument('foo', 'bar');

        $this->assertEquals('bar', $foo);
    }

    /**
     * Sets up the fixture.
     *
     * This method is called before a test is executed.
     *
     * @return  void
     *
     * @since   2.0
     */
    protected function setUp()
    {
        $this->instance = new Dispatcher();
    }
}
