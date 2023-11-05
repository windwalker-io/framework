<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Attributes\Enum;

/**
 * The EnumMeta class.
 */
#[\Attribute(\Attribute::TARGET_CLASS_CONSTANT)]
class Meta
{
    public function __construct(public array $meta)
    {
    }

    /**
     * @return array
     */
    public function getMeta(): array
    {
        return $this->meta;
    }
}
