<?php

declare(strict_types=1);

namespace Windwalker\DI\Test\Stub;

/**
 * Sub class with an additional property, which PHP can not use as the real instance of a StubLazyCounter proxy.
 *
 * Used by ContainerLazyTest. Remove together with those tests.
 */
class StubLazyCounterExtra extends StubLazyCounter
{
    public string $extra = 'extra';
}
