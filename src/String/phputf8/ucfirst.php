<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

//---------------------------------------------------------------
/**
* UTF-8 aware alternative to ucfirst
* Make a string's first character uppercase
* Note: requires utf8_strtoupper
* @param string
* @return string with first character as upper case (if applicable)
* @see http://www.php.net/ucfirst
* @see utf8_strtoupper
* @package utf8
*/
function utf8_ucfirst($str){
    switch ( utf8_strlen($str) ) {
        case 0:
            return '';
        break;
        case 1:
            return utf8_strtoupper($str);
        break;
        default:
            preg_match('/^(.{1})(.*)$/us', $str, $matches);
            return utf8_strtoupper($matches[1]).$matches[2];
        break;
    }
}

