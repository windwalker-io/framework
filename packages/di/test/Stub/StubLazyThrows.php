<?php

declare(strict_types=1);

namespace Windwalker\DI\Test\Stub;

use RuntimeException;

/**
 * Throws in constructor to verify error handling of deferred lazy initialization.
 *
 * Used by ContainerLazyTest. Remove together with those tests.
 */
class StubLazyThrows
{
    public string $name = 'throws';

    public function __construct()
    {
        throw new RuntimeException('boom');
    }
}
