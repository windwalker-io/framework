<?php

declare(strict_types=1);

namespace Windwalker\Event\Test\Stub;

/**
 * Plain (non-AbstractEvent) event whose constructor takes named arguments, used by EventEmitterTest to verify
 * that emit() passes string-class arguments to the constructor. Safe to remove with that test.
 */
class StubArgsEvent
{
    public function __construct(public string $value = '') {}

    public function setName(string $name): static
    {
        return $this;
    }
}
