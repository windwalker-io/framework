<?php
/**
 * Part of ww4 project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT.
 * @license    Please see LICENSE file.
 */

namespace Windwalker\String;

/**
 * The StringHelper class.
 *
 * @since  3.2
 */
class Str
{
    const CASE_SENSITIVE = true;

    const CASE_INSENSITIVE = false;

    const ENCODING_DEFAULT_ISO = 'ISO-8859-1';

    const ENCODING_UTF8 = 'UTF-8';

    const ENCODING_US_ASCII = 'US-ASCII';

    /**
     * at
     *
     * @param string $string
     * @param int    $pos
     * @param string $encoding
     *
     * @return string
     */
    public static function getChar($string, $pos, $encoding = null)
    {
        $encoding = $encoding === null ? mb_internal_encoding() : $encoding;

        if (Mbstring::strlen($string, $encoding) < abs($pos)) {
            return '';
        }

        return Mbstring::substr($string, $pos, 1);
    }

    /**
     * between
     *
     * @param string      $string
     * @param string      $start
     * @param string      $end
     * @param int         $offset
     * @param string|null $encoding
     *
     * @return  string
     */
    public static function between(
        $string,
        $start,
        $end,
        $offset = 0,
        $encoding = null
    ) {
        $encoding = $encoding === null ? mb_internal_encoding() : $encoding;

        $startIndex = Mbstring::strpos($string, $start, $offset, $encoding);

        if ($startIndex === false) {
            return '';
        }

        $substrIndex = $startIndex + Mbstring::strlen($start, $encoding);

        $endIndex = Mbstring::strpos($string, $end, $substrIndex, $encoding);

        if ($endIndex === false) {
            return '';
        }

        return Mbstring::substr($string, $substrIndex, $endIndex - $substrIndex);
    }

    /**
     * collapseWhitespaces
     *
     * @param string $string
     *
     * @return  string
     */
    public static function collapseWhitespaces($string)
    {
        $string = preg_replace('/\s\s+/', ' ', $string);

        return trim(preg_replace('/\s+/', ' ', $string));
    }

    /**
     * contains
     *
     * @param string $string
     * @param string $search
     * @param bool   $caseSensitive
     * @param string $encoding
     *
     * @return bool
     */
    public static function contains(
        $string,
        $search,
        $caseSensitive = true,
        $encoding = null
    ) {
        $encoding = $encoding === null ? mb_internal_encoding() : $encoding;

        if ($caseSensitive) {
            return Mbstring::strpos($string, $search, 0, $encoding) !== false;
        } else {
            return Mbstring::stripos($string, $search, 0, $encoding) !== false;
        }
    }

    /**
     * endsWith
     *
     * @param string $string
     * @param string $search
     * @param bool   $caseSensitive
     * @param string $encoding
     *
     * @return bool
     */
    public static function endsWith(
        $string,
        $search,
        $caseSensitive = true,
        $encoding = null
    ) {
        $encoding = $encoding === null ? mb_internal_encoding() : $encoding;

        $stringLength = Mbstring::strlen($string, $encoding);
        $targetLength = Mbstring::strlen($search, $encoding);

        if ($stringLength < $targetLength) {
            return false;
        }

        if (!$caseSensitive) {
            $string = Mbstring::strtoupper($string, $encoding);
            $search = Mbstring::strtoupper($search, $encoding);
        }

        $end = Mbstring::substr($string, -$targetLength, null, $encoding);

        return $end === $search;
    }

    /**
     * startsWith
     *
     * @param string  $string
     * @param string  $target
     * @param boolean $caseSensitive
     * @param string  $encoding
     *
     * @return bool
     */
    public static function startsWith(
        $string,
        $target,
        $caseSensitive = true,
        $encoding = null
    ) {
        $encoding = $encoding === null ? mb_internal_encoding() : $encoding;

        if (!$caseSensitive) {
            $string = Mbstring::strtoupper($string, $encoding);
            $target = Mbstring::strtoupper($target, $encoding);
        }

        return Mbstring::strpos($string, $target, 0, $encoding) === 0;
    }

    /**
     * ensureLeft
     *
     * @param string      $string
     * @param string      $search
     * @param string|null $encoding
     *
     * @return  string
     */
    public static function ensureLeft($string, $search, $encoding = null)
    {
        if (static::startsWith($string, $search, true, $encoding)) {
            return $string;
        }

        return $search . $string;
    }

    /**
     * ensureRight
     *
     * @param string      $string
     * @param string      $search
     * @param string|null $encoding
     *
     * @return  string
     */
    public static function ensureRight($string, $search, $encoding = null)
    {
        if (static::endsWith($string, $search, true, $encoding)) {
            return $string;
        }

        return $string . $search;
    }

    /**
     * hasLowerCase
     *
     * @param string      $string
     * @param string|null $encoding
     *
     * @return  bool
     */
    public static function hasLowerCase($string, $encoding = null)
    {
        return static::match('.*[[:lower:]]', $string, 'msr', $encoding);
    }

    /**
     * hasUpperCase
     *
     * @param string      $string
     * @param string|null $encoding
     *
     * @return  bool
     */
    public static function hasUpperCase($string, $encoding = null)
    {
        return static::match('.*[[:upper:]]', $string, 'msr', $encoding);
    }

    /**
     * match
     *
     * @param string      $pattern
     * @param string      $string
     * @param string|null $option
     * @param string|null $encoding
     *
     * @return  bool
     */
    public static function match($pattern, $string, $option = 'msr', $encoding = null)
    {
        $encoding = $encoding === null ? mb_internal_encoding() : $encoding;

        $encodingBackup = mb_regex_encoding();

        mb_regex_encoding($encoding);

        $result = mb_ereg_match($pattern, $string, $option);

        mb_regex_encoding($encodingBackup);

        return $result;
    }

    /**
     * insert
     *
     * @param string      $string
     * @param string      $insert
     * @param int         $position
     * @param string|null $encoding
     *
     * @return  string
     */
    public static function insert($string, $insert, $position, $encoding = null)
    {
        $encoding = $encoding === null ? mb_internal_encoding() : $encoding;

        $length = Mbstring::strlen($string, $encoding);

        if ($position > $length) {
            return $string;
        }

        $left = Mbstring::substr($string, 0, $position, $encoding);
        $right = Mbstring::substr($string, $position, $length, $encoding);

        return $left . $insert . $right;
    }

    /**
     * isLowerCase
     *
     * @param string $string
     *
     * @return  bool
     */
    public static function isLowerCase($string)
    {
        return static::match('^[[:lower:]]*$', $string);
    }

    /**
     * isUpperCase
     *
     * @param string $string
     *
     * @return  bool
     */
    public static function isUpperCase($string)
    {
        return static::match('^[[:upper:]]*$', $string);
    }

    /**
     * first
     *
     * @param string      $string
     * @param int         $length
     * @param string|null $encoding
     *
     * @return  string
     */
    public static function first($string, $length = 1, $encoding = null)
    {
        if ($string === '' || $length <= 0) {
            return '';
        }

        $encoding = $encoding === null ? mb_internal_encoding() : $encoding;

        return Mbstring::substr($string, 0, $length, $encoding);
    }

    /**
     * last
     *
     * @param string      $string
     * @param int         $length
     * @param string|null $encoding
     *
     * @return  string
     */
    public static function last($string, $length = 1, $encoding = null)
    {
        if ($string === '' || $length <= 0) {
            return '';
        }

        $encoding = $encoding === null ? mb_internal_encoding() : $encoding;

        return Mbstring::substr($string, -$length, null, $encoding);
    }

    /**
     * intersectLeft
     *
     * @param string      $string1
     * @param string      $string2
     * @param string|null $encoding
     *
     * @return  string
     */
    public static function intersectLeft($string1, $string2, $encoding = null)
    {
        $encoding = $encoding === null ? mb_internal_encoding() : $encoding;
        $maxLength = min(Mbstring::strlen($string1, $encoding), Mbstring::strlen($string2, $encoding));
        $intersect = '';

        for ($i = 0; $i <= $maxLength; $i++) {
            $char = Mbstring::substr($string1, $i, 1, $encoding);

            if ($char === Mbstring::substr($string2, $i, 1, $encoding)) {
                $intersect .= $char;
            } else {
                break;
            }
        }

        return $intersect;
    }

    /**
     * intersectRight
     *
     * @param string      $string1
     * @param string      $string2
     * @param string|null $encoding
     *
     * @return  string
     */
    public static function intersectRight($string1, $string2, $encoding = null)
    {
        $encoding = $encoding === null ? mb_internal_encoding() : $encoding;
        $maxLength = min(Mbstring::strlen($string1, $encoding), Mbstring::strlen($string2, $encoding));
        $intersect = '';

        for ($i = 1; $i <= $maxLength; $i++) {
            $char = Mbstring::substr($string1, -$i, 1, $encoding);

            if ($char === Mbstring::substr($string2, -$i, 1, $encoding)) {
                $intersect = $char . $intersect;
            } else {
                break;
            }
        }

        return $intersect;
    }

    /**
     * intersect
     *
     * @see https://en.wikipedia.org/wiki/Longest_common_substring_problem
     * @see https://en.wikibooks.org/wiki/Algorithm_Implementation/Strings/Longest_common_substring#PHP
     *
     * @param string      $string1
     * @param string      $string2
     * @param string|null $encoding
     *
     * @return  string
     */
    public static function intersect($string1, $string2, $encoding = null)
    {
        $encoding = $encoding === null ? mb_internal_encoding() : $encoding;
        $str1Length = Mbstring::strlen($string1, $encoding);
        $str2Length = Mbstring::strlen($string2, $encoding);

        if ($str1Length === 0 || $str2Length === 0) {
            return '';
        }

        $len = 0;
        $end = 0;

        $subsequence = array_fill(0, $str1Length + 1, array_fill(0, $str2Length + 1, 0));

        for ($i = 1; $i <= $str1Length; $i++) {
            for ($j = 1; $j <= $str2Length; $j++) {
                $str1Char = Mbstring::substr($string1, $i - 1, 1, $encoding);
                $str2Char = Mbstring::substr($string2, $j - 1, 1, $encoding);

                if ($str1Char === $str2Char) {
                    $subsequence[$i][$j] = $subsequence[$i - 1][$j - 1] + 1;

                    if ($subsequence[$i][$j] > $len) {
                        $len = $subsequence[$i][$j];
                        $end = $i;
                    }
                } else {
                    $subsequence[$i][$j] = 0;
                }
            }
        }

        return Mbstring::substr($string1, $end - $len, $len, $encoding);
    }

    /**
     * pad
     *
     * @param string      $string
     * @param int         $length
     * @param string      $substring
     * @param string|null $encoding
     *
     * @return  string
     */
    public static function pad(
        $string,
        $length = 0,
        $substring = ' ',
        $encoding = null
    ) {
        $encoding = $encoding === null ? mb_internal_encoding() : $encoding;
        $strLength = Mbstring::strlen($string, $encoding);
        $padding = $length - $strLength;

        return static::doPad($string, (int) floor($padding / 2), (int) ceil($padding / 2), $substring, $encoding);
    }

    /**
     * padLeft
     *
     * @param string      $string
     * @param int         $length
     * @param string      $substring
     * @param string|null $encoding
     *
     * @return  string
     */
    public static function padLeft(
        $string,
        $length = 0,
        $substring = ' ',
        $encoding = null
    ) {
        $encoding = $encoding === null ? mb_internal_encoding() : $encoding;

        return static::doPad($string, $length - Mbstring::strlen($string, $encoding), 0, $substring, $encoding);
    }

    /**
     * padRight
     *
     * @param string      $string
     * @param int         $length
     * @param string      $substring
     * @param string|null $encoding
     *
     * @return  string
     */
    public static function padRight(
        $string,
        $length = 0,
        $substring = ' ',
        $encoding = null
    ) {
        $encoding = $encoding === null ? mb_internal_encoding() : $encoding;

        return static::doPad($string, 0, $length - Mbstring::strlen($string, $encoding), $substring, $encoding);
    }

    /**
     * doPad
     *
     * @param string      $string
     * @param int         $left
     * @param int         $right
     * @param string      $substring
     * @param string|null $encoding
     *
     * @return  string
     */
    private static function doPad(
        $string,
        $left,
        $right,
        $substring,
        $encoding = null
    ) {
        $strLength = Mbstring::strlen($string, $encoding);
        $padLength = Mbstring::strlen($substring, $encoding);
        $paddedLength = $strLength + $left + $right;

        if (!$padLength || $paddedLength <= $strLength) {
            return $string;
        }

        $leftStr = Mbstring::substr(str_repeat($substring, (int) ceil($left / $padLength)), 0, $left, $encoding);
        $rightStr = Mbstring::substr(str_repeat($substring, (int) ceil($right / $padLength)), 0, $right, $encoding);

        return $leftStr . $string . $rightStr;
    }

    /**
     * removeChar
     *
     * @param string      $string
     * @param int         $offset
     * @param int|null    $length
     * @param string|null $encoding
     *
     * @return  string
     */
    public static function removeChar($string, $offset, $length = null, $encoding = null)
    {
        $encoding = $encoding === null ? mb_internal_encoding() : $encoding;

        if (Mbstring::strlen($string, $encoding) < abs($offset)) {
            return $string;
        }

        $length = $length === null ? 1 : $length;

        return Mbstring::substrReplace($string, '', $offset, $length, $encoding);
    }

    /**
     * removeLeft
     *
     * @param string      $string
     * @param string      $search
     * @param string|null $encoding
     *
     * @return  string
     */
    public static function removeLeft($string, $search, $encoding = null)
    {
        if ($string === '') {
            return '';
        }

        if ($search === '') {
            return $string;
        }

        $encoding = $encoding === null ? mb_internal_encoding() : $encoding;

        if (!static::startsWith($string, $search, true, $encoding)) {
            return $string;
        }

        return Mbstring::substr($string, Mbstring::strlen($search), null, $encoding);
    }

    /**
     * removeRight
     *
     * @param string      $string
     * @param string      $search
     * @param string|null $encoding
     *
     * @return  string
     */
    public static function removeRight($string, $search, $encoding = null)
    {
        if ($string === '') {
            return '';
        }

        if ($search === '') {
            return $string;
        }

        $encoding = $encoding === null ? mb_internal_encoding() : $encoding;

        if (!static::endsWith($string, $search, true, $encoding)) {
            return $string;
        }

        return Mbstring::substr($string, 0, -Mbstring::strlen($search), $encoding);
    }

    /**
     * slice
     *
     * @param string      $string
     * @param int         $start
     * @param int|null    $end
     * @param string|null $encoding
     *
     * @return  string
     */
    public static function slice($string, $start, $end = null, $encoding = null)
    {
        $encoding = $encoding === null ? mb_internal_encoding() : $encoding;

        if ($end === null) {
            $length = Mbstring::strlen($string, $encoding);
        } elseif ($end >= 0 && $end <= $start) {
            return '';
        } elseif ($end < 0) {
            $length = Mbstring::strlen($string, $encoding) + $end - $start;
        } else {
            $length = $end - $start;
        }

        return Mbstring::substr($string, $start, $length, $encoding);
    }

    /**
     * substring
     *
     * @param string      $string
     * @param int         $start
     * @param int|null    $end
     * @param string|null $encoding
     *
     * @return  string
     */
    public static function substring($string, $start, $end = null, $encoding = null)
    {
        $encoding = $encoding === null ? mb_internal_encoding() : $encoding;

        if ($end === null) {
            $length = Mbstring::strlen($string, $encoding);
        } elseif ($end >= 0 && $end <= $start) {
            $length = $start - $end;
            $start = $end;
        } elseif ($end < 0) {
            $length = Mbstring::strlen($string, $encoding) + $end - $start;
        } else {
            $length = $end - $start;
        }

        return Mbstring::substr($string, $start, $length, $encoding);
    }

    /**
     * surround
     *
     * @param string       $string
     * @param string|array $substring
     *
     * @return  string
     */
    public static function surround($string, $substring = ['"', '"'])
    {
        $substring = (array) $substring;

        if (empty($substring[1])) {
            $substring[1] = $substring[0];
        }

        return $substring[0] . $string . $substring[1];
    }

    /**
     * toggleCase
     *
     * @param string      $string
     * @param string|null $encoding
     *
     * @return  string
     */
    public static function toggleCase($string, $encoding = null)
    {
        $encoding = $encoding === null ? mb_internal_encoding() : $encoding;

        return preg_replace_callback(
            '/[\S]/u',
            function ($match) use ($encoding) {
                if ($match[0] === Mbstring::strtoupper($match[0], $encoding)) {
                    return Mbstring::strtolower($match[0], $encoding);
                }

                return Mbstring::strtoupper($match[0], $encoding);
            },
            $string
        );
    }

    /**
     * truncate
     *
     * @param string      $string
     * @param int         $length
     * @param string      $suffix
     * @param bool        $wordBreak
     * @param string|null $encoding
     *
     * @return  string
     */
    public static function truncate(
        $string,
        $length,
        $suffix = '',
        $wordBreak = true,
        $encoding = null
    ) {
        $encoding = $encoding === null ? mb_internal_encoding() : $encoding;

        if ($length >= Mbstring::strlen($string, $encoding)) {
            return $string;
        }

        $result = Mbstring::substr($string, 0, $length, $encoding);

        if (!$wordBreak && Mbstring::strpos($result, ' ', 0, $encoding) !== $length) {
            $position = Mbstring::strrpos($result, ' ', 0, $encoding);
            $result = Mbstring::substr($result, 0, $position, $encoding);
        }

        return $result . $suffix;
    }

    /**
     * map
     *
     * @param string      $string
     * @param callable    $callback
     * @param string|null $encoding
     *
     * @return  string
     */
    public static function map($string, callable $callback, $encoding = null)
    {
        $result = [];

        foreach (Mbstring::strSplit($string, 1, $encoding) as $key => $char) {
            if ($callback instanceof \Closure) {
                $result[] = $callback($char, $key);
            } else {
                $result[] = $callback($char);
            }
        }

        return implode('', $result);
    }

    /**
     * filter
     *
     * @param string      $string
     * @param callable    $callback
     * @param string|null $encoding
     *
     * @return  string
     */
    public static function filter($string, callable $callback, $encoding = null)
    {
        return static::map(
            $string,
            function ($char, &$key) use ($callback) {
                if ($callback instanceof \Closure) {
                    $result = $callback($char, $key);
                } else {
                    $result = $callback($char);
                }

                return $result ? $char : '';
            },
            $encoding
        );
    }

    /**
     * reject
     *
     * @param string      $string
     * @param callable    $callback
     * @param string|null $encoding
     *
     * @return  string
     */
    public static function reject($string, callable $callback, $encoding = null)
    {
        return static::filter(
            $string,
            function ($char, &$key) use ($callback) {
                if ($callback instanceof \Closure) {
                    $result = $callback($char, $key);
                } else {
                    $result = $callback($char);
                }

                return !$result;
            },
            $encoding
        );
    }
}
