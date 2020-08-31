<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Filter\Rule;

use Windwalker\Filter\AbstractFilterVar;

/**
 * The UrlAddress class.
 */
class UrlAddress extends AbstractFilterVar
{
    public function getFilterName(): int
    {
        return FILTER_SANITIZE_URL;
    }

    public function getOptions(): ?int
    {
        return FILTER_FLAG_QUERY_REQUIRED | FILTER_FLAG_PATH_REQUIRED;
    }
}
