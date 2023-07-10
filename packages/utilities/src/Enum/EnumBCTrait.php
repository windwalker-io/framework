<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Enum;

/**
 * Trait EnumBCTrait
 */
trait EnumBCTrait
{
    public function equals($variable = null): bool
    {
        return $variable === $this;
    }
}
