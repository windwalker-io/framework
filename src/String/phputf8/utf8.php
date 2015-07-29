<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

/**
* Put the current directory in this constant
*/
if ( !defined('UTF8') ) {
    define('UTF8',dirname(__FILE__));
}

/**
* If string overloading is active, it will break many of the
* native implementations. mbstring.func_overload must be set
* to 0, 1 or 4 in php.ini (string overloading disabled).
* Also need to check we have the correct internal mbstring
* encoding
*/
if ( extension_loaded('mbstring')) {
    if ( ini_get('mbstring.func_overload') & MB_OVERLOAD_STRING ) {
        trigger_error('String functions are overloaded by mbstring',E_USER_ERROR);
    }
    mb_internal_encoding('UTF-8');
}

/**
* Check whether PCRE has been compiled with UTF-8 support
*/
$UTF8_ar = array();
if ( preg_match('/^.{1}$/u',"ñ",$UTF8_ar) != 1 ) {
    trigger_error('PCRE is not compiled with UTF-8 support',E_USER_ERROR);
}
unset($UTF8_ar);


/**
* Load the smartest implementations of utf8_strpos, utf8_strrpos
* and utf8_substr
*/
if ( !defined('UTF8_CORE') ) {
    if ( function_exists('mb_substr') ) {
        require_once UTF8 . '/mbstring/core.php';
    } else {
        require_once UTF8 . '/utils/unicode.php';
        require_once UTF8 . '/native/core.php';
    }
}

/**
* Load the native implementation of utf8_substr_replace
*/
require_once UTF8 . '/substr_replace.php';

/**
* You should now be able to use all the other utf_* string functions
*/
