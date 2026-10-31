<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Stub\AttributeScan\Nested;

use Windwalker\Utilities\Test\Stub\Attributes\StubMarker;

/**
 * Scanned fixture in a sub directory carrying StubMarker, used by AttributesAccessorTest::scanDirAndRunAttributes.
 *
 * Remove it together with that test.
 */
#[StubMarker('beta')]
class Beta {}
