<?php

declare(strict_types=1);

namespace Windwalker\Event\Test\Provider;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Event\Event;
use Windwalker\Event\Provider\DecorateListenerProvider;
use Windwalker\Event\Provider\SimpleListenerProvider;

/**
 * The DecorateListenerProviderTest class.
 */
class DecorateListenerProviderTest extends TestCase
{
    #[Test]
    public function delegatesListenerLookupToDecoratedProvider(): void
    {
        $fn = static fn() => null;
        $inner = new SimpleListenerProvider(['foo' => [$fn]]);
        $provider = new DecorateListenerProvider($inner);

        self::assertSame($inner, $provider->getProvider());
        self::assertSame([$fn], iterator_to_array($provider->getListenersForEvent(new Event('foo')), false));
    }
}
