<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Attributes\Enum;

/**
 * The EnumString class.
 */
#[\Attribute(\Attribute::TARGET_CLASS_CONSTANT)]
class Hidden
{
    public function __construct()
    {
        //
    }
}
