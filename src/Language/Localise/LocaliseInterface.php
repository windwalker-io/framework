<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Language\Localise;

/**
 * Interface LocaliseInterface
 */
interface LocaliseInterface
{
    /**
     * getPluralSuffixes
     *
     * @param int $count
     *
     * @return  string
     */
    public function getPluralSuffix($count = 1);
}
