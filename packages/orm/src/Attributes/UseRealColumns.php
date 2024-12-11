<?php

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

/**
 * Use real DB columns when Query auto-join and prepare columns.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class UseRealColumns
{
}
