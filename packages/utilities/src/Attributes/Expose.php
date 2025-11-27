<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Attributes;

/**
 * When an object is being serialized or dumped, properties with this attribute will be force exposed.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Expose
{
}
