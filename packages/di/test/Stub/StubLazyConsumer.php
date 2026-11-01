<?php

declare(strict_types=1);

namespace Windwalker\DI\Test\Stub;

/**
 * Consumer with interface, concrete and optional dependencies for lazy injection tests.
 *
 * Used by ContainerLazyTest. Remove together with those tests.
 */
class StubLazyConsumer
{
    public function __construct(
        public StubLazyContract $contract,
        public StubLazyCounter $counter,
        public ?StubLazyFinal $optional = null,
    ) {}
}
