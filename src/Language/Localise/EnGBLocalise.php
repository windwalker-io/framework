<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Language\Localise;

/**
 * Class EnGBLocalise
 *
 * @since 2.0
 */
class EnGBLocalise implements LocaliseInterface
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
