<?php

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
    public function filter(mixed $value): mixed
    {
        return $value;
    }

    /**
     * @inheritDoc
     */
    public function test(mixed $value, bool $strict = false): bool
    {
        return true;
    }
}
