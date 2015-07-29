<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

//---------------------------------------------------------------
/**
* UTF-8 aware alternative to strcasecmp
* A case insensivite string comparison
* Note: requires utf8_strtolower
* @param string
* @param string
* @return int
* @see http://www.php.net/strcasecmp
* @see utf8_strtolower
* @package utf8
*/
function utf8_strcasecmp($strX, $strY) {
    $strX = utf8_strtolower($strX);
    $strY = utf8_strtolower($strY);
    return strcmp($strX, $strY);
}

