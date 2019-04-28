<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    GNU General Public License version 2 or later.
 */

// Simple fix for Blade escape
if (!function_exists('e')) {
    function e($string, $doubleEncode = true)
    {
        return htmlspecialchars((string) $string, ENT_QUOTES, 'UTF-8', $doubleEncode);
    }
}
