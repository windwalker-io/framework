<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2022 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Http;

use Windwalker\Data\Collection;
use Windwalker\Uri\UriHelper;

/**
 * The FormData class.
 */
class FormData extends Collection
{
    public function __toString(): string
    {
        return UriHelper::buildQuery($this->storage);
    }
}
