<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker;

use Windwalker\Data\Collection;

if (!function_exists('\Windwalker\collect')) {
    function collect($storage = []): Collection
    {
        return Collection::wrap($storage);
    }
}
