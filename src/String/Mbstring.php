<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\String;

/**
 * String handling class for utf-8 data
 * Wraps the phputf8 library
 * All functions assume the validity of utf-8 strings.
 *
 * @method  static int|bool strpos(string $str, string $search, int $offset = 0, string $encoding = null)
 * @method  static int|bool strrpos(string $str, string $search, int $offset = 0, string $encoding = null)
 * @method  static string substr(string $str, $offset, int $length = null, string $encoding = null)
 * @method  static string strtolower(string $str, string $encoding = null)
 * @method  static string strtoupper(string $str, string $encoding = null)
 * @method  static int    strlen(string $str, string $encoding = null)
 * @method  static string stristr(string $str, string $search, string $encoding = null)
 * @method  static mixed  stripos(string $haystack, string $needle, int $offset = 0, string $encoding = null)
 * @method  static string strrchr(string $haystack, string $needle, $part = false, string $encoding = null)
 * @method  static string strrichr(string $haystack, string $needle, $part = false, string $encoding = null)
 * @method  static int    strripos(string $haystack, string $needle, int $offset = 0, string $encoding = null)
 * @method  static string strstr(string $haystack, string $needle, $part = false, string $encoding = null)
 * @method  static string chr(string $code, string $encoding = null)
 * @method  static string ord(string $s, string $encoding = null)
 * @method  static string parseStr(string $encoded_string, array &$result)
 * @method  static string convertCase(string $s, $mode, string $encoding = null)
 * @method  static string detectEncoding(string $str, $encodingList = null, bool $strict = false)
 * @method  static mixed  detectOrder($encodingList = null)
 * @method  static string eregReplace(string $pattern, string $replacement, string $string, string $option = "msr")
 * @method  static string eregiReplace(string $pattern, string $replacement, string $string, string $option = "msr")
 *
 * @since  2.0
 */
abstract class Mbstring
{
    const CASE_SENSITIVE = true;

    const CASE_INSENSITIVE = false;

    const ENCODING_DEFAULT_ISO = 'ISO-8859-1';

    const ENCODING_UTF8 = 'UTF-8';

    const ENCODING_US_ASCII = 'US-ASCII';

    /**
     * Tests whether a string contains only 7bit ASCII bytes.
     * You might use this to conditionally check whether a string
     * needs handling as UTF-8 or not, potentially offering performance
     * benefits by using the native PHP equivalent if it's just ASCII e.g.;
     *
     * ``` php
     * if (String::is_ascii($someString))
     * {
     *     // It's just ASCII - use the native PHP version
     *     $someString = strtolower($someString);
     * }
     * else
     * {
     *     $someString = String::strtolower($someString);
     * }
     * ```
     *
     * @param   string $str The string to test.
     *
     * @return  boolean True if the string is all ASCII
     *
     * @since   2.0
     */
    public static function isAscii($str)
    {
        // Search for any bytes which are outside the ASCII range...
        return (preg_match('/(?:[^\x00-\x7F])/', $str) !== 1);
    }

    /**
     * __callStatic
     *
     * @param string $name
     * @param array  $args
     *
     * @return  mixed
     * @throws \BadMethodCallException
     */
    public static function __callStatic($name, $args)
    {
        $underscoreName = trim(strtolower(preg_replace('#([A-Z])#', '_$1', $name)));

        $function = 'mb_' . $underscoreName;

        if (function_exists($function)) {
            return call_user_func_array($function, $args);
        }

        throw new \BadMethodCallException(sprintf('Call to undefined method: %s::%s', get_called_class(), $name));
    }

    /**
     * str_ireplace
     *
     * @param      $search
     * @param      $replace
     * @param      $str
     * @param null $count
     * @param null $encoding
     *
     * @return  mixed
     */
    public static function strIreplace($search, $replace, $str, $count = null, $encoding = null)
    {
        $encoding = $encoding === null ? mb_internal_encoding() : $encoding;

        if (!is_array($search)) {
            $slen = strlen($search);

            if ($slen == 0) {
                return $str;
            }

            $lendif = strlen($replace) - strlen($search);
            $search = static::strtolower($search, $encoding);
            $search = preg_quote($search, '/');
            $lstr = static::strtolower($str, $encoding);
            $i = 0;
            $matched = 0;

            while (preg_match('/(.*)' . $search . '/Us', $lstr, $matches)) {
                if ($count !== null && $i === $count) {
                    break;
                }

                $mlen = strlen($matches[0]);
                $lstr = substr($lstr, $mlen);

                $str = substr_replace($str, $replace, $matched + strlen($matches[1]), $slen);
                $matched += $mlen + $lendif;
                $i++;
            }

            return $str;
        } else {
            $keys = array_keys($search);

            foreach ($keys as $k) {
                if (is_array($replace)) {
                    if (array_key_exists($k, $replace)) {
                        $str = static::strIreplace($search[$k], $replace[$k], $str, $count, $encoding);
                    } else {
                        $str = static::strIreplace($search[$k], '', $str, $count, $encoding);
                    }
                } else {
                    $str = static::strIreplace($search[$k], $replace, $str, $count, $encoding);
                }
            }

            return $str;
        }
    }

    /**
     * str_split
     *
     * @see  http://php.net/manual/en/function.str-split.php#117112
     *
     * @param     $string
     * @param int $length
     *
     * @return  array|bool
     */
    public static function strSplit($string, $length = 1, $encoding = null)
    {
        $encoding = $encoding === null ? mb_internal_encoding() : $encoding;

        if ($length === 1) {
            return preg_split("//u", $string, -1, PREG_SPLIT_NO_EMPTY);
        }

        if ($length > 1) {
            $return_value = [];
            $string_length = static::strlen($string, $encoding);

            for ($i = 0; $i < $string_length; $i += $length) {
                $return_value[] = static::substr($string, $i, $length, $encoding);
            }

            return $return_value;
        }

        return false;
    }

    /**
     * UTF-8/LOCALE aware alternative to strcasecmp
     * A case insensitive string comparison
     *
     * @param   string $str1 string 1 to compare
     * @param   string $str2 string 2 to compare
     *
     * @return  integer   < 0 if str1 is less than str2; > 0 if str1 is greater than str2, and 0 if they are equal.
     *
     * @see     http://www.php.net/strcasecmp
     * @since   2.0
     */
    public static function strcasecmp($str1, $str2, $encoding = null)
    {
        $encoding = $encoding === null ? mb_internal_encoding() : $encoding;

        return static::strcmp(static::strtoupper($str1, $encoding), static::strtoupper($str2, $encoding));
    }

    /**
     * A case sensitive string comparison
     *
     * @param   string $str1 string 1 to compare
     * @param   string $str2 string 2 to compare
     *
     * @return  integer  < 0 if str1 is less than str2; > 0 if str1 is greater than str2, and 0 if they are equal.
     *
     * @since   2.0
     */
    public static function strcmp($str1, $str2)
    {
        return strcmp($str1, $str2);
    }

    /**
     * UTF-8 aware alternative to strcspn
     * Find length of initial segment not matching mask
     *
     * @param   string  $str    The string to process
     * @param   string  $mask   The mask
     * @param   integer $start  Optional starting character position (in characters)
     * @param   integer $length Optional length
     *
     * @return  integer  The length of the initial segment of str1 which does not contain any of the characters in str2
     *
     * @see     http://www.php.net/strcspn
     * @since   2.0
     */
    public static function strcspn(
        $str,
        $mask,
        $start = 0,
        $length = null,
        $encoding = null
    ) {
        $encoding = $encoding === null ? mb_internal_encoding() : $encoding;

        if (empty($mask) || $mask === '') {
            return 0;
        }

        $mask = preg_replace('!([\\\\\\-\\]\\[/^])!', '\\\${1}', $mask);

        if ($start !== null || $length !== null) {
            $str = static::substr($str, $start, $length, $encoding);
        }

        preg_match('/^[^' . $mask . ']+/u', $str, $matches);

        if (isset($matches[0])) {
            return static::strlen($matches[0], $encoding);
        }

        return 0;
    }

    /**
     * UTF-8 aware alternative to strrev
     * Reverse a string
     *
     * @param   string $str String to be reversed
     *
     * @return  string   The string in reverse character order
     *
     * @see     http://www.php.net/strrev
     * @since   2.0
     */
    public static function strrev($str)
    {
        preg_match_all('/./us', $str, $matches);

        return implode('', array_reverse($matches[0]));
    }

    /**
     * UTF-8 aware alternative to strspn
     * Find length of initial segment matching mask
     *
     * @param   string  $str    The haystack
     * @param   string  $mask   The mask
     * @param   integer $start  Start optional
     * @param   integer $length Length optional
     *
     * @return  integer
     *
     * @see     http://www.php.net/strspn
     * @since   2.0
     */
    public static function strspn(
        $str,
        $mask,
        $start = 0,
        $length = null,
        $encoding = null
    ) {
        $encoding = $encoding === null ? mb_internal_encoding() : $encoding;

        $mask = preg_replace('!([\\\\\\-\\]\\[/^])!', '\\\${1}', $mask);

        // Fix for $start but no $length argument.
        if ($start !== null && $length === null) {
            $length = static::strlen($str, $encoding);
        }

        if ($start !== null || $length !== null) {
            $str = static::substr($str, $start, $length, $encoding);
        }

        preg_match('/^[' . $mask . ']+/u', $str, $matches);

        if (isset($matches[0])) {
            return static::strlen($matches[0], $encoding);
        }

        return 0;
    }

    /**
     * UTF-8 aware substr_replace
     * Replace text within a portion of a string
     *
     * @param   string  $str    The haystack
     * @param   string  $repl   The replacement string
     * @param   integer $start  Start
     * @param   integer $length Length (optional)
     *
     * @return  string
     *
     * @see     http://www.php.net/substr_replace
     * @since   2.0
     */
    public static function substrReplace($str, $repl, $start, $length = null, $encoding = null)
    {
        $encoding = $encoding === null ? mb_internal_encoding() : $encoding;

        preg_match_all('/./us', $str, $ar);
        preg_match_all('/./us', $repl, $rar);

        if ($length === null) {
            $length = static::strlen($str, $encoding);
        }

        array_splice($ar[0], $start, $length, $rar[0]);

        return implode('', $ar[0]);
    }

    /**
     * UTF-8 aware replacement for ltrim()
     *
     * Strip whitespace (or other characters) from the beginning of a string
     * You only need to use this if you are supplying the charlist
     * optional arg and it contains UTF-8 characters. Otherwise ltrim will
     * work normally on a UTF-8 string
     *
     * @param   string $str      The string to be trimmed
     * @param   string $charlist The optional charlist of additional characters to trim
     *
     * @return  string  The trimmed string
     *
     * @see     http://www.php.net/ltrim
     * @since   2.0
     */
    public static function ltrim($str, $charlist = null)
    {
        if ($charlist === null) {
            return ltrim($str);
        }

        if ($charlist === '') {
            return $str;
        }

        // quote charlist for use in a characterclass
        $charlist = preg_replace('!([\\\\\\-\\]\\[/^])!', '\\\${1}', $charlist);

        return preg_replace('/^[' . $charlist . ']+/u', '', $str);
    }

    /**
     * UTF-8 aware replacement for rtrim()
     * Strip whitespace (or other characters) from the end of a string
     * You only need to use this if you are supplying the charlist
     * optional arg and it contains UTF-8 characters. Otherwise rtrim will
     * work normally on a UTF-8 string
     *
     * @param   string $str      The string to be trimmed
     * @param   string $charlist The optional charlist of additional characters to trim
     *
     * @return  string  The trimmed string
     *
     * @see     http://www.php.net/rtrim
     * @since   2.0
     */
    public static function rtrim($str, $charlist = null)
    {
        if ($charlist === null) {
            return rtrim($str);
        }

        if ($charlist === '') {
            return $str;
        }

        // quote charlist for use in a characterclass
        $charlist = preg_replace('!([\\\\\\-\\]\\[/^])!', '\\\${1}', $charlist);

        return preg_replace('/[' . $charlist . ']+$/u', '', $str);
    }

    /**
     * UTF-8 aware replacement for trim()
     * Strip whitespace (or other characters) from the beginning and end of a string
     * Note: you only need to use this if you are supplying the charlist
     * optional arg and it contains UTF-8 characters. Otherwise trim will
     * work normally on a UTF-8 string
     *
     * @param   string $str      The string to be trimmed
     * @param   string $charlist The optional charlist of additional characters to trim
     *
     * @return  string  The trimmed string
     *
     * @see     http://www.php.net/trim
     * @since   2.0
     */
    public static function trim($str, $charlist = null)
    {
        if ($charlist === null) {
            return trim($str);
        }

        if ($charlist === '') {
            return $str;
        }

        return static::ltrim(static::rtrim($str, $charlist), $charlist);
    }

    /**
     * UTF-8 aware alternative to ucfirst
     * Make a string's first character uppercase or all words' first character uppercase
     *
     * @param   string $str       String to be processed
     * @param   string $delimiter The words delimiter (null means do not split the string)
     *
     * @return  string  If $delimiter is null, return the string with first character as upper case (if applicable)
     *                  else consider the string of words separated by the delimiter, apply the ucfirst to each words
     *                  and return the string with the new delimiter
     *
     * @see     http://www.php.net/ucfirst
     * @since   2.0
     */
    public static function ucfirst($str, $encoding = null)
    {
        $encoding = $encoding === null ? mb_internal_encoding() : $encoding;

        switch (static::strlen($str, $encoding)) {
            case 0:
                return '';
                break;
            case 1:
                return static::strtoupper($str, $encoding);
                break;
            default:
                preg_match('/^(.{1})(.*)$/us', $str, $matches);

                return static::strtoupper($matches[1], $encoding) . $matches[2];
                break;
        }
    }

    /**
     * lcfirst
     *
     * @param string      $str
     * @param string|null $encoding
     *
     * @return  string
     */
    public static function lcfirst($str, $encoding = null)
    {
        $encoding = $encoding === null ? mb_internal_encoding() : $encoding;

        switch (static::strlen($str, $encoding)) {
            case 0:
                return '';
                break;
            case 1:
                return static::strtolower($str, $encoding);
                break;
            default:
                preg_match('/^(.{1})(.*)$/us', $str, $matches);

                return static::strtolower($matches[1], $encoding) . $matches[2];
                break;
        }
    }

    /**
     * UTF-8 aware alternative to ucwords
     * Uppercase the first character of each word in a string
     *
     * @param   string $str String to be processed
     *
     * @return  string  String with first char of each word uppercase
     *
     * @see     http://www.php.net/ucwords
     * @since   2.0
     */
    public static function ucwords($str, $encoding = null)
    {
        $encoding = $encoding === null ? mb_internal_encoding() : $encoding;

        // Note: [\x0c\x09\x0b\x0a\x0d\x20] matches;
        // form feeds, horizontal tabs, vertical tabs, linefeeds and carriage returns
        // This corresponds to the definition of a "word" defined at http://www.php.net/ucwords
        $pattern = '/(^|([\x0c\x09\x0b\x0a\x0d\x20]+))([^\x0c\x09\x0b\x0a\x0d\x20]{1})[^\x0c\x09\x0b\x0a\x0d\x20]*/u';

        return preg_replace_callback(
            $pattern,
            function ($matches) use ($encoding) {
                $leadingws = $matches[2];
                $ucfirst = static::strtoupper($matches[3], $encoding);
                $ucword = static::substrReplace(ltrim($matches[0]), $ucfirst, 0, 1);

                return $leadingws . $ucword;
            },
            $str
        );
    }

    /**
     * substr_count
     *
     * @param string      $string
     * @param string      $search
     * @param bool        $caseSensitive
     * @param string|null $encoding
     *
     * @return  int
     */
    public static function substrCount(
        $string,
        $search,
        $caseSensitive = true,
        $encoding = null
    ) {
        $encoding = $encoding === null ? mb_internal_encoding() : $encoding;

        if (!$caseSensitive) {
            $string = static::strtoupper($string);
            $search = static::strtoupper($search);
        }

        return mb_substr_count($string, $search, $encoding);
    }

    /**
     * Transcode a string.
     *
     * @param   string $source The string to transcode.
     * @param   string $from   The source encoding.
     * @param   string $to     The target encoding.
     *
     * @return  string  The transcoded string.
     *
     * @link    https://bugs.php.net/bug.php?id=48147
     *
     * @since   2.0
     */
    public static function convertEncoding($source, $from, $to)
    {
        if ($source === '') {
            return $source;
        }

        return mb_convert_encoding($source, $to, $from);
    }

    /**
     * Tests a string as to whether it's valid UTF-8 and supported by the Unicode standard.
     *
     * Note: this function has been modified to simple return true or false.
     *
     * @param   string $str UTF-8 encoded string.
     *
     * @return  boolean  true if valid
     *
     * @author  <hsivonen@iki.fi>
     * @see     http://hsivonen.iki.fi/php-utf8/
     * @see     compliant
     * @since   2.0
     */
    public static function isUtf8($str)
    {
        $mState = 0;     // cached expected number of octets after the current octet
        // until the beginning of the next UTF8 character sequence
        $mUcs4 = 0;     // cached Unicode character
        $mBytes = 1;     // cached expected number of octets in the current sequence

        $len = strlen($str);

        for ($i = 0; $i < $len; $i++) {
            $in = ord($str{$i});

            if ($mState === 0) {
                // When mState is zero we expect either a US-ASCII character or a
                // multi-octet sequence.
                if (0 === (0x80 & $in)) {
                    // US-ASCII, pass straight through.
                    $mBytes = 1;
                } elseif (0xC0 === (0xE0 & $in)) {
                    // First octet of 2 octet sequence
                    $mUcs4 = $in;
                    $mUcs4 = ($mUcs4 & 0x1F) << 6;
                    $mState = 1;
                    $mBytes = 2;
                } elseif (0xE0 === (0xF0 & $in)) {
                    // First octet of 3 octet sequence
                    $mUcs4 = $in;
                    $mUcs4 = ($mUcs4 & 0x0F) << 12;
                    $mState = 2;
                    $mBytes = 3;
                } elseif (0xF0 === (0xF8 & $in)) {
                    // First octet of 4 octet sequence
                    $mUcs4 = $in;
                    $mUcs4 = ($mUcs4 & 0x07) << 18;
                    $mState = 3;
                    $mBytes = 4;
                } elseif (0xF8 === (0xFC & $in)) {
                    /* First octet of 5 octet sequence.
                    *
                    * This is illegal because the encoded codepoint must be either
                    * (a) not the shortest form or
                    * (b) outside the Unicode range of 0-0x10FFFF.
                    * Rather than trying to resynchronize, we will carry on until the end
                    * of the sequence and let the later error handling code catch it.
                    */
                    $mUcs4 = $in;
                    $mUcs4 = ($mUcs4 & 0x03) << 24;
                    $mState = 4;
                    $mBytes = 5;
                } elseif (0xFC === (0xFE & $in)) {
                    // First octet of 6 octet sequence, see comments for 5 octet sequence.
                    $mUcs4 = $in;
                    $mUcs4 = ($mUcs4 & 1) << 30;
                    $mState = 5;
                    $mBytes = 6;
                } else {
                    /* Current octet is neither in the US-ASCII range nor a legal first
                     * octet of a multi-octet sequence.
                     */
                    return false;
                }
            } else {
                // When mState is non-zero, we expect a continuation of the multi-octet
                // sequence
                if (0x80 === (0xC0 & $in)) {
                    // Legal continuation.
                    $shift = ($mState - 1) * 6;
                    $tmp = $in;
                    $tmp = ($tmp & 0x0000003F) << $shift;
                    $mUcs4 |= $tmp;

                    /*
                     * End of the multi-octet sequence. mUcs4 now contains the final
                     * Unicode codepoint to be output
                     */
                    if (0 === --$mState) {
                        /*
                        * Check for illegal sequences and codepoints.
                        */
                        // From Unicode 3.1, non-shortest form is illegal
                        if (((2 === $mBytes) && ($mUcs4 < 0x0080)) ||
                            ((3 === $mBytes) && ($mUcs4 < 0x0800)) ||
                            ((4 === $mBytes) && ($mUcs4 < 0x10000)) ||
                            (4 < $mBytes) ||
                            // From Unicode 3.2, surrogate characters are illegal
                            (($mUcs4 & 0xFFFFF800) === 0xD800) ||
                            // Codepoints outside the Unicode range are illegal
                            ($mUcs4 > 0x10FFFF)
                        ) {
                            // @codeCoverageIgnoreStart
                            return false;
                            // @codeCoverageIgnoreEnd
                        }

                        //initialize UTF8 cache
                        $mState = 0;
                        $mUcs4 = 0;
                        $mBytes = 1;
                    }
                } else {
                    /*
                     *((0xC0 & (*in) != 0x80) && (mState != 0))
                     * Incomplete multi-octet sequence.
                     */
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Tests whether a string complies as UTF-8. This will be much
     * faster than utf8_is_valid but will pass five and six octet
     * UTF-8 sequences, which are not supported by Unicode and
     * so cannot be displayed correctly in a browser. In other words
     * it is not as strict as utf8_is_valid but it's faster. If you use
     * it to validate user input, you place yourself at the risk that
     * attackers will be able to inject 5 and 6 byte sequences (which
     * may or may not be a significant risk, depending on what you are
     * are doing)
     *
     * @param   string $str UTF-8 string to check
     *
     * @return  boolean  TRUE if string is valid UTF-8
     *
     * @see     isUtf8
     * @see     http://www.php.net/manual/en/reference.pcre.pattern.modifiers.php#54805
     * @since   2.0
     */
    public static function compliant($str)
    {
        if ($str === '') {
            return true;
        }

        // If even just the first character can be matched, when the /u
        // modifier is used, then it's valid UTF-8. If the UTF-8 is somehow
        // invalid, nothing at all will match, even if the string contains
        // some valid sequences
        return (preg_match('/^.{1}/us', $str, $ar) === 1);
    }

    /**
     * Converts Unicode sequences to UTF-8 string
     *
     * @param   string $str Unicode string to convert
     *
     * @return  string  UTF-8 string
     *
     * @since   2.0
     */
    public static function unicodeToUtf8($str)
    {
        return preg_replace_callback(
            '/\\\\u([0-9a-fA-F]{4})/',
            function ($match) {
                return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
            },
            $str
        );
    }

    /**
     * Converts Unicode sequences to UTF-16 string
     *
     * @param   string $str Unicode string to convert
     *
     * @return  string  UTF-16 string
     *
     * @since   2.0
     */
    public static function unicodeToUtf16($str)
    {
        return preg_replace_callback(
            '/\\\\u([0-9a-fA-F]{4})/',
            function ($match) {
                return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UTF-16BE');
            },
            $str
        );
    }

    /**
     * shuffle
     *
     * @param string $string
     * @param string $encoding
     *
     * @return  string
     *
     * @since  3.2
     */
    public static function shuffle($string, $encoding = null)
    {
        $encoding = $encoding === null ? mb_internal_encoding() : $encoding;

        $chars = static::strSplit($string, 1, $encoding);

        shuffle($chars);

        return implode('', $chars);
    }
}
