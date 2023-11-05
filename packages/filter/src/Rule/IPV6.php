<?php

declare(strict_types=1);

namespace Windwalker\Filter\Rule;

use Windwalker\Filter\AbstractRegexFilter;

/**
 * The IPAddress class.
 */
class IPV6 extends AbstractRegexFilter
{
    /**
     * @inheritDoc
     */
    public function getRegex(): string
    {
        return '/[^A-F0-9:\[\]]/i';
    }

    /**
     * @inheritDoc
     */
    public function test(mixed $value, bool $strict = false): bool
    {
        return filter_var(
            FILTER_VALIDATE_IP,
            FILTER_FLAG_IPV6
        );
    }
}
