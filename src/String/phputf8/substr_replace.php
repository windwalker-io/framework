<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

//---------------------------------------------------------------
/**
* UTF-8 aware substr_replace.
* Note: requires utf8_substr to be loaded
* @see http://www.php.net/substr_replace
* @see utf8_strlen
* @see utf8_substr
*/
function utf8_substr_replace($str, $repl, $start , $length = NULL ) {
    preg_match_all('/./us', $str, $ar);
    preg_match_all('/./us', $repl, $rar);
    if( $length === NULL ) {
        $length = utf8_strlen($str);
    }
    array_splice( $ar[0], $start, $length, $rar[0] );
    return join('',$ar[0]);
}
