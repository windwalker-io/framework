<?php

declare(strict_types=1);

namespace Windwalker\DI\Test\Stub;

/**
 * Interface dependency used to verify global lazy mode with non-proxyable parameter types.
 *
 * Used by ContainerLazyTest. Remove together with those tests.
 */
interface StubLazyContract
{
    public function name(): string;
}
