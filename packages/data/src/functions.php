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

function collect($storage = []): Collection
{
    return Collection::wrap($storage);
}
