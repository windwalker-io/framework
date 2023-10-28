<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Filter\Rule;

use Windwalker\Filter\AbstractRegexFilter;

/**
 * The WordFilter class.
 */
class Words extends AbstractRegexFilter
{
    /**
     * @inheritDoc
     */
    public function getRegex(): string
    {
        return '/[^A-Z_]/i';
    }
}
