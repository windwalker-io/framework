<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Enum;

/**
 * Trait EnumBCTrait
 */
trait EnumBCTrait
{
    /**
     * @param $variable
     *
     * @return  bool
     *
     * @deprecated  Use 8.2 enums compare
     */
    public function equals($variable = null): bool
    {
        return $variable === $this;
    }
}
