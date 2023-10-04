<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
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
