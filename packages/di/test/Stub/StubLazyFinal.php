<?php

declare(strict_types=1);

namespace Windwalker\DI\Test\Stub;

/**
 * Final class that must fall back to eager creation under global lazy mode.
 *
 * Used by ContainerLazyTest. Remove together with those tests.
 */
final class StubLazyFinal
{
    public function hello(): string
    {
        return 'final';
    }
}
