<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Stub\Attributes;

use Attribute;

/**
 * Marker attribute applied to StubMarkedParent/StubMarkedChild/StubMarkedEnum, used by AttributesAccessorTest
 * and TraitOptionsTest.
 *
 * Remove it together with AttributesAccessorTest.
 */
#[Attribute(Attribute::TARGET_ALL)]
class StubMarker
{
    public function __construct(public string $name = '') {}
}
