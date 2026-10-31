<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Stub\AttributeScan;

use Windwalker\Utilities\Test\Stub\Attributes\StubMarker;

/**
 * Scanned fixture carrying StubMarker, used by AttributesAccessorTest::scanDirAndRunAttributes tests.
 *
 * Remove it together with that test.
 */
#[StubMarker('alpha')]
class Alpha {}
