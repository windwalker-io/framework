<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Stub\Attributes;

/**
 * Enum fixture with an attributed case, used by AttributesAccessorTest.
 *
 * Remove it together with AttributesAccessorTest.
 */
enum StubMarkedEnum
{
    #[StubMarker('case')]
    case One;

    case Two;
}
