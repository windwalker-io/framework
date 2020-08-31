<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Filter\Rule;

use Windwalker\Filter\AbstractFilter;

/**
 * The RawValue class.
 */
class RawValue extends AbstractFilter
{
    /**
     * @inheritDoc
     */
    public function filter($value)
    {
        return $value;
    }

    /**
     * @inheritDoc
     */
    public function test($value, bool $strict = false): bool
    {
        return true;
    }
}
