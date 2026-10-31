<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Stub\Attributes;

/**
 * Parent fixture carrying class-level, constant, property and method attributes, used by AttributesAccessorTest.
 *
 * Remove it together with AttributesAccessorTest.
 */
#[StubMarker('parent')]
class StubMarkedParent
{
    #[StubMarker('const')]
    public const MARKED = 'marked';

    public const PLAIN = 'plain';

    #[StubMarker('prop')]
    public string $marked = '';

    public string $plain = '';

    #[StubSubMarker('method')]
    public function marked(): void {}

    public function plain(): void {}
}
