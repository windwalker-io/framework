<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

// phpcs:disable

//---------------------------------------------------------------
/**
 * UTF-8 aware substr_replace.
 * Note: requires utf8_substr to be loaded
 * @see http://www.php.net/substr_replace
 * @see utf8_strlen
 * @see utf8_substr
 */
function utf8_substr_replace($str, $repl, $start, $length = null)
{
    preg_match_all('/./us', $str, $ar);
    preg_match_all('/./us', $repl, $rar);
    if ($length === null) {
        $length = utf8_strlen($str);
    }
    array_splice($ar[0], $start, $length, $rar[0]);

    return join('', $ar[0]);
}
