<?php

declare(strict_types=1);

namespace Windwalker\DI\Test\Stub;

/**
 * Concrete implementation bound to StubLazyContract in ContainerLazyTest.
 *
 * Remove together with those tests.
 */
class StubLazyContractImpl implements StubLazyContract
{
    public string $label = 'impl';

    public function name(): string
    {
        return $this->label;
    }
}
