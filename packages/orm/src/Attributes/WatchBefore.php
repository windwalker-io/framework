<?php

declare(strict_types=1);

namespace Windwalker\ORM\Attributes;

use Attribute;

/**
 * The Watch class.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PROPERTY)]
class WatchBefore extends Watch
{
    public function __construct(callable|string $columnOrHandler, int $options = 0)
    {
        $options |= static::BEFORE_SAVE;

        parent::__construct($columnOrHandler, $options);
    }
}
