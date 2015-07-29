<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

//---------------------------------------------------------------
/**
* UTF-8 aware alternative to stristr
* Find first occurrence of a string using case insensitive comparison
* Note: requires utf8_strtolower
* @param string
* @param string
* @return int
* @see http://www.php.net/strcasecmp
* @see utf8_strtolower
* @package utf8
*/
function utf8_stristr($str, $search) {

    if ( strlen($search) == 0 ) {
        return $str;
    }

    $lstr = utf8_strtolower($str);
    $lsearch = utf8_strtolower($search);
    //Windwalker SPECIFIC FIX - BEGIN
    preg_match('/^(.*)'.preg_quote($lsearch, '/').'/Us',$lstr, $matches);
    //Windwalker SPECIFIC FIX - END

    if ( count($matches) == 2 ) {
        return substr($str, strlen($matches[1]));
    }

    return FALSE;
}
