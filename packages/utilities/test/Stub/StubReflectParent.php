<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Stub;

/**
 * Parent fixture holding a private property, used by ReflectAccessorTest to verify
 * that ReflectAccessor can read parent-private values.
 *
 * Remove it once ReflectAccessorTest no longer references StubReflectSubject.
 */
class StubReflectParent
{
    private string $parentPrivate = 'base';
}
