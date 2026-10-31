<?php

declare(strict_types=1);

namespace Windwalker\Event\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Event\BaseEvent;
use Windwalker\Event\Event;
use Windwalker\Event\Test\Stub\StubBaseEvent;

/**
 * The BaseEventTest class.
 */
class BaseEventTest extends TestCase
{
    #[Test]
    public function getNameFallsBackToClassName(): void
    {
        self::assertSame(StubBaseEvent::class, new StubBaseEvent()->getName());
        self::assertSame('custom', new StubBaseEvent()->setName('custom')->getName());
    }

    #[Test]
    public function wrapStringCreatesEventNamedByString(): void
    {
        $event = StubBaseEvent::wrap('hello', ['flower' => 'Rose']);

        self::assertInstanceOf(StubBaseEvent::class, $event);
        self::assertSame('hello', $event->getName());
        self::assertSame('Rose', $event->flower);
    }

    #[Test]
    public function wrapStringUsingExistingClassCreatesThatClass(): void
    {
        $event = BaseEvent::wrap(StubBaseEvent::class);

        self::assertInstanceOf(StubBaseEvent::class, $event);
        self::assertSame(StubBaseEvent::class, $event->getName());
    }

    #[Test]
    public function wrapEventObjectMergesArgumentsAndReturnsSameInstance(): void
    {
        $event = new StubBaseEvent();

        self::assertSame($event, BaseEvent::wrap($event, ['tree' => 'Pine']));
        self::assertSame('Pine', $event->tree);
    }

    #[Test]
    public function createBuildsEventWithArguments(): void
    {
        $event = StubBaseEvent::create(['flower' => 'Rose']);

        self::assertSame('Rose', $event->flower);
    }

    #[Test]
    public function mirrorWithEventClassKeepsArgumentsAndResetsStopped(): void
    {
        $event = new StubBaseEvent();
        $event->flower = 'Rose';
        $event->stopPropagation();

        $mirror = $event->mirror(StubBaseEvent::class, ['tree' => 'Pine']);

        self::assertNotSame($event, $mirror);
        self::assertInstanceOf(StubBaseEvent::class, $mirror);
        self::assertFalse($mirror->isPropagationStopped());
        self::assertSame('Rose', $mirror->flower);
        self::assertSame('Pine', $mirror->tree);
    }

    #[Test]
    public function mirrorWithPlainNameCreatesGenericEvent(): void
    {
        $mirror = new StubBaseEvent()->mirror('other', ['extra' => 1]);

        self::assertInstanceOf(Event::class, $mirror);
        self::assertSame('other', $mirror->getName());
        self::assertSame(
            ['flower' => 'Sakura', 'tree' => 'Oak', 'extra' => 1],
            $mirror->getArguments()
        );
    }

    #[Test]
    public function getArgumentsExcludesNameAndStopped(): void
    {
        $event = new StubBaseEvent();
        $event->setName('foo');

        self::assertSame(['flower' => 'Sakura', 'tree' => 'Oak'], $event->getArguments());
    }

    #[Test]
    public function fillMergesNamedArguments(): void
    {
        $event = new StubBaseEvent();

        self::assertSame($event, $event->fill(flower: 'Rose'));
        self::assertSame('Rose', $event->flower);
    }

    #[Test]
    public function setArgumentsResetsToDefaultsBeforeMerging(): void
    {
        $event = new StubBaseEvent();
        $event->flower = 'Rose';
        $event->tree = 'Pine';

        $event->setArguments(['flower' => 'Lily']);

        self::assertSame('Lily', $event->flower);
        self::assertSame('Oak', $event->tree);
    }

    #[Test]
    public function clearRestoresDefaultPropertyValues(): void
    {
        $event = new StubBaseEvent();
        $event->flower = 'Rose';

        self::assertSame($event, $event->clear());
        self::assertSame('Sakura', $event->flower);
    }

    #[Test]
    public function stopPropagationMarksEventAsStopped(): void
    {
        $event = new StubBaseEvent();

        self::assertFalse($event->isPropagationStopped());

        $event->stopPropagation();

        self::assertTrue($event->isPropagationStopped());
    }
}
