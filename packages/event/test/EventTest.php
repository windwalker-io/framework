<?php

declare(strict_types=1);

namespace Windwalker\Event\Test;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Event\Event;

/**
 * The EventTest class.
 */
class EventTest extends TestCase
{
    protected ?Event $instance;

    /**
     * @see  Event::merge
     */
    #[Test]
    public function mergeCombinesArguments(): void
    {
        $result = $this->instance->merge(['flower' => 'Rose', 'tree' => 'Oak']);

        self::assertSame($this->instance, $result);
        self::assertSame(
            ['flower' => 'Rose', 'starship' => 'Enterprise', 'tree' => 'Oak'],
            $this->instance->getArguments()
        );
    }

    /**
     * @see  Event::jsonSerialize
     */
    #[Test]
    public function jsonSerializeReturnsNameAndArguments(): void
    {
        self::assertSame(
            [
                'name' => 'onHelloWelcome',
                'stopped' => false,
                'arguments' => ['flower' => 'Sakura', 'starship' => 'Enterprise'],
            ],
            $this->instance->jsonSerialize()
        );
    }

    /**
     * @see  Event::isPropagationStopped
     */
    #[Test]
    public function isPropagationStoppedReflectsState(): void
    {
        self::assertFalse($this->instance->isPropagationStopped());

        $this->instance->stopPropagation();

        self::assertTrue($this->instance->isPropagationStopped());
    }

    /**
     * @see  Event::__construct
     */
    #[Test]
    public function constructSetsNameAndArguments(): void
    {
        $event = new Event('foo', ['bar' => 'baz']);

        self::assertSame('foo', $event->getName());
        self::assertSame(['bar' => 'baz'], $event->getArguments());
        self::assertSame(Event::class, new Event()->getName());
    }

    /**
     * @see  Event::unserialize
     */
    #[Test]
    public function unserializeRestoresNameAndArguments(): void
    {
        $event = new Event();

        $event->__unserialize(['restored', ['flower' => 'Rose'], true]);

        self::assertSame('restored', $event->getName());
        self::assertSame(['flower' => 'Rose'], $event->getArguments());
        self::assertTrue($event->isPropagationStopped());
    }

    /**
     * @see  Event::setArguments
     */
    #[Test]
    public function setArgumentsReplacesArguments(): void
    {
        $result = $this->instance->setArguments(['tree' => 'Oak']);

        self::assertSame($this->instance, $result);
        self::assertSame(['tree' => 'Oak'], $this->instance->getArguments());
    }

    /**
     * @see  Event::mirror
     */
    #[Test]
    public function mirrorCreatesNewEventWithMergedArguments(): void
    {
        $event = $this->instance->mirror('LiveLong', ['flower' => 'rose']);
        self::assertNotSame($this->instance, $event);
        self::assertEquals(
            'LiveLong',
            $event->getName()
        );

        self::assertEquals(
            [
                'flower' => 'rose',
                'starship' => 'Enterprise',
            ],
            $event->getArguments()
        );
    }

    /**
     * @see  Event::getName
     */
    #[Test]
    public function getNameReturnsEventName(): void
    {
        self::assertSame('onHelloWelcome', $this->instance->getName());
    }

    /**
     * @see  Event::serialize
     */
    #[Test]
    public function serializeRoundTripKeepsNameAndArguments(): void
    {
        $s = serialize($this->instance);

        $event = unserialize($s);

        self::assertEquals(
            'onHelloWelcome',
            $event->getName()
        );

        self::assertEquals(
            [
                'flower' => 'Sakura',
                'starship' => 'Enterprise',
            ],
            $event->getArguments()
        );
    }

    /**
     * @see  Event::getArguments
     */
    #[Test]
    public function getArgumentsReturnsAllArguments(): void
    {
        self::assertSame(
            ['flower' => 'Sakura', 'starship' => 'Enterprise'],
            $this->instance->getArguments()
        );
    }

    /**
     * @see  Event::stopPropagation
     */
    #[Test]
    public function stopPropagationMarksEventStopped(): void
    {
        $this->instance->stopPropagation();

        self::assertTrue($this->instance->isPropagationStopped());

        $mirror = $this->instance->mirror('another');

        self::assertFalse($mirror->isPropagationStopped());
    }

    /**
     * @see  Event::wrap
     */
    #[Test]
    public function wrapCreatesEventFromObject(): void
    {
        $event = Event::wrap('hello', ['flower' => 'Rose']);

        self::assertInstanceOf(Event::class, $event);
        self::assertSame('hello', $event->getName());
        self::assertSame(['flower' => 'Rose'], $event->getArguments());

        $wrapped = Event::wrap($this->instance, ['tree' => 'Oak']);

        self::assertSame($this->instance, $wrapped);
        self::assertSame('Oak', $wrapped['tree']);
    }

    /**
     * @see  Event::clear
     */
    #[Test]
    public function clearRemovesArguments(): void
    {
        $result = $this->instance->clear();

        self::assertSame($this->instance, $result);
        self::assertSame([], $this->instance->getArguments());
        self::assertSame('onHelloWelcome', $this->instance->getName());
    }

    #[Test]
    public function magicGetterAndSetterAccessArguments()
    {
        $this->instance->setFlower('Olive');
        $flower = $this->instance->getFlower('Olive');

        self::assertEquals('Olive', $flower);
        self::assertEquals(
            [
                'flower' => 'Olive',
                'starship' => 'Enterprise',
            ],
            $this->instance->getArguments()
        );
    }

    #[Before]
    protected function createEvent(): void
    {
        $this->instance = new Event(
            'onHelloWelcome',
            [
                'flower' => 'Sakura',
                'starship' => 'Enterprise',
            ]
        );
    }
}
