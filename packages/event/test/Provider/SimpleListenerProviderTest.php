<?php

declare(strict_types=1);

namespace Windwalker\Event\Test\Provider;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Event\Event;
use Windwalker\Event\Provider\SimpleListenerProvider;
use Windwalker\Event\Test\Stub\StubFlowerEvent;
use Windwalker\Event\Test\Stub\StubTreeEvent;

/**
 * The SimpleListenerProviderTest class.
 */
class SimpleListenerProviderTest extends TestCase
{
    #[Test]
    public function eventInterfaceMatchesListenersByEventName(): void
    {
        $a = static fn() => 'a';
        $b = static fn() => 'b';
        $provider = new SimpleListenerProvider(['foo' => [$a], 'bar' => [$b]]);

        self::assertSame([$b], iterator_to_array($provider->getListenersForEvent(new Event('bar')), false));
        self::assertSame([], iterator_to_array($provider->getListenersForEvent(new Event('none')), false));
    }

    #[Test]
    public function plainObjectMatchesListenersByClassName(): void
    {
        $a = static fn() => 'a';
        $provider = new SimpleListenerProvider(
            [StubTreeEvent::class => [static fn() => 'tree'], StubFlowerEvent::class => [$a]]
        );

        self::assertSame([$a], iterator_to_array($provider->getListenersForEvent(new StubFlowerEvent()), false));
    }

    #[Test]
    public function addAppendsListenersPerEvent(): void
    {
        $a = static fn() => 'a';
        $b = static fn() => 'b';
        $provider = new SimpleListenerProvider([]);

        self::assertSame($provider, $provider->add('foo', $a));
        $provider->add('foo', $b);

        self::assertSame(['foo' => [$a, $b]], $provider->getListeners());
    }
}
