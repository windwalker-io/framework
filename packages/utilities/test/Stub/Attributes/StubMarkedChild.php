<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Stub\Attributes;

/**
 * Child fixture whose own attribute is combined with its parent's, used by AttributesAccessorTest.
 *
 * Remove it together with AttributesAccessorTest.
 */
#[StubSubMarker('child')]
class StubMarkedChild extends StubMarkedParent {}
