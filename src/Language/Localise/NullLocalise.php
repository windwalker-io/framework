<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Language\Localise;

/**
 * Class NullLocalise
 *
 * @since 2.0
 */
class NullLocalise implements LocaliseInterface
{
    /**
     * getPluralSuffixes
     *
     * @param int $count
     *
     * @return  string
     */
    public function getPluralSuffix($count = 1)
    {
        if ($count == 0) {
            return '0';
        } elseif ($count == 1) {
            return '1';
        }

        return 'more';
    }
}
