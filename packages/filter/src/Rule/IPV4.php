<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Filter\Rule;

use Windwalker\Filter\AbstractRegexFilter;

/**
 * The IPAddress class.
 */
class IPV4 extends AbstractRegexFilter
{
    /**
     * @inheritDoc
     */
    public function getRegex(): string
    {
        return '/[^0-9.]/i';
    }

    /**
     * @inheritDoc
     */
    public function test($value, bool $strict = false): bool
    {
        return filter_var(
            FILTER_VALIDATE_IP,
            FILTER_FLAG_IPV4
        );
    }
}
