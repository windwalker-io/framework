<?php
/**
 * @copyright  Copyright (C) 2019 LYRASOFT Source Matters.
 * @license    LGPL-2.0-or-later.txt
 */

namespace Windwalker\Event\Test;

use Windwalker\Event\Event;

/**
 * Tests for the Event class.
 *
 * @since  2.0
 */
class EventTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Object under tests.
     *
     * @var    Event
     *
     * @since  2.0
     */
    private $instance;

    /**
     * testConstruct
     *
     * @return  void
     */
    public function testConstruct()
    {
        $foo = 'foo';
        $args = ['foo' => &$foo];

        $event = new Event('onTest', $args);

        $event->setArgument('foo', 'bar');

        $this->assertEquals('bar', $foo);
    }

    /**
     * Test the getName method.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function testGetName()
    {
        $this->assertEquals('test', $this->instance->getName());
    }

    /**
     * Test the getArgument method.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function testGetArgument()
    {
        $this->assertFalse($this->instance->getArgument('non-existing', false));

        $object = new \stdClass();
        $array = [
            'foo' => 'bar',
            'test' => [
                'foo' => 'bar',
                'test' => 'test',
            ],
        ];

        $arguments = [
            'string' => 'bar',
            'object' => $object,
            'array' => $array,
        ];

        $event = new Event('onTest', $arguments);

        $this->assertEquals('bar', $event->getArgument('string'));
        $this->assertSame($object, $event->getArgument('object'));
        $this->assertSame($array, $event->getArgument('array'));
    }

    /**
     * Test the hasArgument method.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function testHasArgument()
    {
        $this->assertFalse($this->instance->hasArgument('non-existing'));

        /** @var $event \Windwalker\Event\Event */
        $event = $this->getMockForAbstractClass('Windwalker\Event\Event', ['test', ['foo' => 'bar']]);

        $this->assertTrue($event->hasArgument('foo'));
    }

    /**
     * Test the getArguments method.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function testGetArguments()
    {
        $this->assertEmpty($this->instance->getArguments());

        $object = new \stdClass();
        $array = [
            'foo' => 'bar',
            'test' => [
                'foo' => 'bar',
                'test' => 'test',
            ],
        ];

        $arguments = [
            'string' => 'bar',
            'object' => $object,
            'array' => $array,
        ];

        $args = ['test', $arguments];

        /** @var $event \Windwalker\Event\Event */
        $event = new Event('onTest', $arguments);

        $this->assertSame($arguments, $event->getArguments());
    }

    /**
     * Test the isStopped method.
     * An immutable event shoudln't be stopped, otherwise it won't trigger.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function testIsStopped()
    {
        $this->assertFalse($this->instance->isStopped());
    }

    /**
     * Test the count method.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function testCount()
    {
        $this->assertCount(0, $this->instance);

        $event = $this->getMockForAbstractClass(
            'Windwalker\Event\Event',
            [
                'test',
                [
                    'foo' => 'bar',
                    'test' => ['test'],
                ],
            ]
        );

        $this->assertCount(2, $event);
    }

    /**
     * Test the serialize and unserialize methods.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function testSerializeUnserialize()
    {
        $object = new \stdClass();
        $array = [
            'foo' => 'bar',
            'test' => [
                'foo' => 'bar',
                'test' => 'test',
            ],
        ];

        $arguments = [
            'string' => 'bar',
            'object' => $object,
            'array' => $array,
        ];

        $event = new Event('onTest', $arguments);

        $serialized = serialize($event);

        $unserialized = unserialize($serialized);

        $this->assertEquals($event, $unserialized);
    }

    /**
     * Test the offsetExists method.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function testOffsetExists()
    {
        $this->assertFalse(isset($this->instance['foo']));

        $event = $this->getMockForAbstractClass('Windwalker\Event\Event', ['test', ['foo' => 'bar']]);

        $this->assertTrue(isset($event['foo']));
    }

    /**
     * Test the offsetGet method.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function testOffsetGet()
    {
        $this->assertNull($this->instance['foo']);

        $object = new \stdClass();
        $array = [
            'foo' => 'bar',
            'test' => [
                'foo' => 'bar',
                'test' => 'test',
            ],
        ];

        $arguments = [
            'string' => 'bar',
            'object' => $object,
            'array' => $array,
        ];

        $event = new Event('onTest', $arguments);

        $this->assertEquals('bar', $event['string']);
        $this->assertSame($object, $event['object']);
        $this->assertSame($array, $event['array']);
    }

    /**
     * Test the addArgument method.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function testAddArgument()
    {
        $object = new \stdClass();

        $array = [
            'test' => [
                'foo' => 'bar',
                'test' => 'test',
            ],
        ];

        $this->instance->addArgument('object', $object);
        $this->assertTrue($this->instance->hasArgument('object'));
        $this->assertSame($object, $this->instance->getArgument('object'));

        $this->instance->addArgument('array', $array);
        $this->assertTrue($this->instance->hasArgument('array'));
        $this->assertSame($array, $this->instance->getArgument('array'));
    }

    /**
     * Test the addArgument method when the argument already exists, it should be untouched.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function testAddArgumentExisting()
    {
        $this->instance->addArgument('foo', 'bar');
        $this->instance->addArgument('foo', 'foo');

        $this->assertTrue($this->instance->hasArgument('foo'));
        $this->assertEquals('bar', $this->instance->getArgument('foo'));
    }

    /**
     * Test the setArgument method.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function testSetArgument()
    {
        $object = new \stdClass();

        $array = [
            'test' => [
                'foo' => 'bar',
                'test' => 'test',
            ],
        ];

        $this->instance->setArgument('object', $object);
        $this->assertTrue($this->instance->hasArgument('object'));
        $this->assertSame($object, $this->instance->getArgument('object'));

        $this->instance->setArgument('array', $array);
        $this->assertTrue($this->instance->hasArgument('array'));
        $this->assertSame($array, $this->instance->getArgument('array'));
    }

    /**
     * Test the setArgument method when the argument already exists, it should be overriden.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function testSetArgumentExisting()
    {
        $this->instance->setArgument('foo', 'bar');
        $this->instance->setArgument('foo', 'foo');

        $this->assertTrue($this->instance->hasArgument('foo'));
        $this->assertEquals('foo', $this->instance->getArgument('foo'));
    }

    /**
     * Test the removeArgument method.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function testRemoveArgument()
    {
        $this->assertNull($this->instance->removeArgument('non-existing'));

        $this->instance->addArgument('foo', 'bar');

        $old = $this->instance->removeArgument('foo');

        $this->assertEquals('bar', $old);
        $this->assertFalse($this->instance->hasArgument('foo'));
    }

    /**
     * Test the clearArguments method.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function testClearArguments()
    {
        $this->assertEmpty($this->instance->clearArguments());

        $arguments = [
            'test' => [
                'foo' => 'bar',
                'test' => 'test',
            ],
            'foo' => new \stdClass(),
        ];

        $event = new Event('test', $arguments);

        $self = $event->clearArguments();

        $this->assertSame($self, $event);
        $this->assertFalse($event->hasArgument('test'));
        $this->assertFalse($event->hasArgument('foo'));
    }

    /**
     * Test the stop method.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function testStop()
    {
        $this->assertFalse($this->instance->isStopped());

        $this->instance->stop();

        $this->assertTrue($this->instance->isStopped());
    }

    /**
     * Test the offsetSet method.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function testOffsetSet()
    {
        $this->instance['foo'] = 'bar';

        $this->assertTrue($this->instance->hasArgument('foo'));
        $this->assertEquals('bar', $this->instance->getArgument('foo'));

        $argument = [
            'test' => [
                'foo' => 'bar',
                'test' => 'test',
            ],
            'foo' => new \stdClass(),
        ];

        $this->instance['foo'] = $argument;
        $this->assertTrue($this->instance->hasArgument('foo'));
        $this->assertSame($argument, $this->instance->getArgument('foo'));
    }

    /**
     * Test the offsetSet method exception.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function testOffsetSetException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->instance[] = 'bar';
    }

    /**
     * Test the offsetUnset method.
     *
     * @return  void
     *
     * @since   2.0
     */
    public function testOffsetUnset()
    {
        // No exception.
        unset($this->instance['foo']);

        $this->instance['foo'] = 'bar';
        unset($this->instance['foo']);

        $this->assertFalse($this->instance->hasArgument('foo'));
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
    protected function setUp(): void
    {
        $this->instance = new Event('test');
    }
}
