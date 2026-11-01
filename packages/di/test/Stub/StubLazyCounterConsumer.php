<?php

declare(strict_types=1);

namespace Windwalker\DI\Test\Stub;

/**
 * Consumer injecting a StubLazyCounter dependency.
 *
 * Used by ContainerLazyTest. Remove together with those tests.
 */
class StubLazyCounterConsumer
{
    public function __construct(public StubLazyCounter $counter) {}
}
