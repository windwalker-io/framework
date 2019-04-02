<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Filesystem\Comparator;

interface FileComparatorInterface
{
    public function compare($current, $key, $iterator);
}
