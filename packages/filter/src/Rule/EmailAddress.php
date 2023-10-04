<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Filter\Rule;

use Windwalker\Filter\AbstractFilterVar;

/**
 * The Email class.
 */
class EmailAddress extends AbstractFilterVar
{
    public function getFilterName(): int
    {
        return FILTER_SANITIZE_EMAIL;
    }

    public function test(mixed $value, bool $strict = false): bool
    {
        return (bool) filter_var((string) $value, FILTER_VALIDATE_EMAIL);
    }

    public function getOptions(): ?int
    {
        return null;
    }
}
