<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Filter;

use Windwalker\Utilities\Compare\CompareHelper;

/**
 * The AbstractFilter class.
 */
abstract class AbstractFilter implements FilterInterface, ValidatorInterface
{
    /**
     * @inheritDoc
     */
    public function test($value, bool $strict = false): bool
    {
        return CompareHelper::compare($this->filter($value), $value, '=', $strict);
    }
}
