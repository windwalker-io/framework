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
 * The Required class.
 */
class Required extends AbstractFilter
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
        return $value !== '' && $value !== null;
    }
}
