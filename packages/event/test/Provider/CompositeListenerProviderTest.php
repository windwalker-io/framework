<?php

declare(strict_types=1);

namespace Windwalker\Event\Test\Provider;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Event\Event;
use Windwalker\Event\Provider\CompositeListenerProvider;
use Windwalker\Event\Provider\SimpleListenerProvider;
use Windwalker\Event\Provider\SubscribableListenerProvider;

use function Windwalker\disposable;

/**
 * The CompositeListenerProviderTest class.
 */
class CompositeListenerProviderTest extends TestCase
{
    #[Test]
    public function createReturnsSameCompositeInstance(): void
    {
        $composite = new CompositeListenerProvider();

        self::assertSame($composite, CompositeListenerProvider::create($composite));
    }

    #[Test]
    public function createWrapsSubscribableProviderAsMainProvider(): void
    {
        $main = new SubscribableListenerProvider();
        $fn = static fn() => null;
        $main->on('foo', $fn);

        $composite = CompositeListenerProvider::create($main);

        self::assertSame([$fn], iterator_to_array($composite->getListenersForEvent(new Event('foo')), false));
    }

    #[Test]
    public function createAppendsOtherProvidersAfterMainProvider(): void
    {
        $fn = static fn() => null;
        $composite = CompositeListenerProvider::create(new SimpleListenerProvider(['foo' => [$fn]]));

        self::assertSame([$fn], iterator_to_array($composite->getListenersForEvent(new Event('foo')), false));
    }

    #[Test]
    public function createWithNullCreatesEmptyComposite(): void
    {
        $composite = CompositeListenerProvider::create();

        self::assertSame([], iterator_to_array($composite->getListenersForEvent(new Event('foo')), false));
    }

    #[Test]
    public function getListenersForEventYieldsMainThenAppendedProviders(): void
    {
        $main = static fn() => 'main';
        $extra = static fn() => 'extra';
        $composite = new CompositeListenerProvider(null, [new SimpleListenerProvider(['foo' => [$extra]])]);
        $composite->on('foo', $main);

        self::assertSame(
            [$main, $extra],
            iterator_to_array($composite->getListenersForEvent(new Event('foo')), false)
        );
    }

    #[Test]
    public function resetProvidersRemovesAppendedProviders(): void
    {
        $composite = new CompositeListenerProvider();
        $composite->appendProvider(new SimpleListenerProvider(['foo' => [static fn() => null]]));

        $composite->resetProviders();

        self::assertSame([], iterator_to_array($composite->getListenersForEvent(new Event('foo')), false));
    }

    #[Test]
    public function disposableListenerIsRemovedAfterBeingYielded(): void
    {
        $count = 0;
        $composite = new CompositeListenerProvider();
        $composite->on(
            'foo',
            disposable(
                static function () use (&$count) {
                    $count++;
                }
            )
        );

        foreach ($composite->getListenersForEvent(new Event('foo')) as $listener) {
            $listener();
        }

        self::assertSame([], iterator_to_array($composite->getListenersForEvent(new Event('foo')), false));
        self::assertSame(1, $count);
    }

    #[Test]
    public function offWithoutListenerRemovesWholeEvent(): void
    {
        $composite = new CompositeListenerProvider();
        $composite->on('foo', static fn() => null);
        $composite->on('bar', $bar = static fn() => null);

        self::assertSame($composite, $composite->off('foo'));
        self::assertSame($composite, $composite->off('missing'));

        self::assertSame([], iterator_to_array($composite->getListenersForEvent(new Event('foo')), false));
        self::assertSame([$bar], iterator_to_array($composite->getListenersForEvent(new Event('bar')), false));
    }

    #[Test]
    public function offAcceptsEventObjectAndListenerCallable(): void
    {
        $a = static fn() => 'a';
        $b = static fn() => 'b';
        $composite = new CompositeListenerProvider();
        $composite->on('foo', $a);
        $composite->on('foo', $b);

        $composite->off(new Event('foo'), $a);

        self::assertSame([$b], iterator_to_array($composite->getListenersForEvent(new Event('foo')), false));
    }

    #[Test]
    public function removeDropsListenerFromEveryEvent(): void
    {
        $a = static fn() => 'a';
        $composite = new CompositeListenerProvider();
        $composite->on('foo', $a);
        $composite->on('bar', $a);

        $composite->remove($a);

        self::assertSame([], iterator_to_array($composite->getListenersForEvent(new Event('foo')), false));
        self::assertSame([], iterator_to_array($composite->getListenersForEvent(new Event('bar')), false));
    }

    #[Test]
    public function cloneCopiesProvidersIndependently(): void
    {
        $composite = new CompositeListenerProvider(null, [new SimpleListenerProvider([])]);
        $composite->on('foo', $a = static fn() => 'a');

        $clone = clone $composite;
        $clone->off('foo');

        self::assertSame([$a], iterator_to_array($composite->getListenersForEvent(new Event('foo')), false));
        self::assertSame([], iterator_to_array($clone->getListenersForEvent(new Event('foo')), false));
    }
}
