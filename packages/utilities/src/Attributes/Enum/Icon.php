<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Attributes\Enum;

/**
 * The EnumIcon class.
 */
#[\Attribute(\Attribute::TARGET_CLASS_CONSTANT)]
class Icon
{
    public function __construct(public string $icon)
    {
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }
}
