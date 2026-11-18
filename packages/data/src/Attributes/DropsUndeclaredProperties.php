<?php

declare(strict_types=1);

namespace Windwalker\Data\Attributes;

/**
 * Mark a class using RecordTrait to silently drop values that have no matching declared
 * property when merging/wrapping, instead of letting PHP raise a dynamic property warning.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class DropsUndeclaredProperties
{
    //
}
