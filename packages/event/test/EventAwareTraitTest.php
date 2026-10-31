<?php

declare(strict_types=1);

namespace Windwalker\Event\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Event\DispatcherAwareInterface;
use Windwalker\Event\EventAwareTrait;
use Windwalker\Event\EventEmitter;

/**
 * The EventAwareTraitTest class.
 */
class EventAwareTraitTest extends TestCase
{
    #[Test]
    public function getEventDispatcherLazilyCreatesSingleEmitter(): void
    {
        $aware = $this->createAware();

        self::assertInstanceOf(EventEmitter::class, $aware->getEventDispatcher());
        self::assertSame($aware->getEventDispatcher(), $aware->getEventDispatcher());
    }

    #[Test]
    public function onAndEmitDelegateToDispatcher(): void
    {
        $aware = $this->createAware();
        $count = 0;

        self::assertSame(
            $aware,
            $aware->on(
                'foo',
                static function () use (&$count) {
                    $count++;
                }
            )
        );
        $aware->emit('foo');

        self::assertSame(1, $count);
    }

    #[Test]
    public function subscribeRegistersPublicMethodsOnDispatcher(): void
    {
        $aware = $this->createAware();
        $subscriber = new class {
            public int $count = 0;

            public function foo(): void
            {
                $this->count++;
            }
        };

        self::assertSame($aware, $aware->subscribe($subscriber));
        $aware->emit('foo');

        self::assertSame(1, $subscriber->count);
    }

    #[Test]
    public function addEventDealerAcceptsDispatcherOrDispatcherAware(): void
    {
        $aware = $this->createAware();
        $dealerA = new EventEmitter();
        $dealerB = $this->createAware();
        $countA = 0;
        $countB = 0;
        $dealerA->on(
            'foo',
            static function () use (&$countA) {
                $countA++;
            }
        );
        $dealerB->on(
            'foo',
            static function () use (&$countB) {
                $countB++;
            }
        );

        self::assertSame($aware, $aware->addEventDealer($dealerA));
        $aware->addEventDealer($dealerB);
        $aware->emit('foo');

        self::assertSame(1, $countA);
        self::assertSame(1, $countB);
    }

    private function createAware(): object
    {
        return new class implements DispatcherAwareInterface {
            use EventAwareTrait;
        };
    }
}
