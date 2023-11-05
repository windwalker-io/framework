<?php

declare(strict_types=1);

namespace Windwalker\Filter\Rule;

use Windwalker\Filter\AbstractRegexFilter;

/**
 * The Cmd class.
 */
class Cmd extends AbstractRegexFilter
{
    /**
     * @inheritDoc
     */
    public function getRegex(): string
    {
        return '/[^A-Z0-9_\.-]/i';
    }
}
