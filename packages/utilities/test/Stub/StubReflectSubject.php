<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Stub;

use Attribute;

/**
 * Fixture with public/protected/uninitialized properties, a protected method and an attribute,
 * used by ReflectAccessorTest.
 *
 * Remove it once ReflectAccessorTest no longer references it.
 */
#[Attribute]
class StubReflectSubject extends StubReflectParent
{
    public int $visible = 1;

    protected string $hidden = 'secret';

    protected ?int $uninitialized;

    protected function secretGreet(string $a, string $b): string
    {
        return $a . ' ' . $b;
    }
}
