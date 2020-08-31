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
 * The Alnum class.
 */
class Alnum extends AbstractRegexFilter
{
    protected string $type = self::TYPE_REPLACE;

    /**
     * @inheritDoc
     */
    public function getRegex(): string
    {
        return '/[^A-Z0-9]/i';
    }
}
