<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Stub\Attributes;

use Attribute;

/**
 * Subclass of StubMarker used to verify IS_INSTANCEOF attribute matching in AttributesAccessorTest.
 *
 * Remove it together with AttributesAccessorTest.
 */
#[Attribute(Attribute::TARGET_ALL)]
class StubSubMarker extends StubMarker {}
