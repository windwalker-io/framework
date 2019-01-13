<?php
/**
 * Part of phoenix project.
 *
 * @copyright  Copyright (C) 2019 $Asikart.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Utilities;

use Windwalker\String\Mbstring;

/**
 * The ArrConverter class.
 *
 * @since  3.5
 */
class ArrConverter
{
    /**
     * Transpose a two-dimensional matrix array.
     *
     * @param  array $array An array with two level.
     *
     * @return array An pivoted array.
     */
    public static function transpose(array $array): array
    {
        $new   = [];
        $keys  = array_keys($array);

        foreach ($keys as $k => $val) {
            foreach ((array) $array[$val] as $k2 => $v2) {
                $new[$k2][$val] = $v2;
            }
        }

        return $new;
    }

    /**
     * Same as ArrayHelper::pivot().
     * From:
     *          [0] => Array
     *             (
     *                 [value] => aaa
     *                 [text] => aaa
     *             )
     *         [1] => Array
     *             (
     *                 [value] => bbb
     *                 [text] => bbb
     *             )
     * To:
     *         [value] => Array
     *             (
     *                 [0] => aaa
     *                 [1] => bbb
     *             )
     *         [text] => Array
     *             (
     *                 [0] => aaa
     *                 [1] => bbb
     *             )
     *
     * @param   array $array An array with two level.
     *
     * @return  array An pivoted array.
     */
    public static function pivotBySort(array $array): array
    {
        $new    = [];
        $array2 = $array;
        $first  = array_shift($array2);

        foreach ($array as $k => $v) {
            foreach ((array) $first as $k2 => $v2) {
                $new[$k2][$k] = $array[$k][$k2];
            }
        }

        return $new;
    }

    /**
     * Pivot $origin['prefix_xxx'] to $target['prefix']['xxx'].
     *
     * @param   string $prefix A prefix text.
     * @param   array  $origin Origin array to pivot.
     * @param   array  $target A target array to store pivoted value.
     *
     * @return  array  Pivoted array.
     */
    public static function groupPrefix(string $prefix, $origin, $target = null)
    {
        $target = is_object($target) ? (object) $target : (array) $target;

        foreach ((array) $origin as $key => $row) {
            if (strpos($key, $prefix) === 0) {
                $key2 = Mbstring::substr($key, Mbstring::strlen($prefix));
                $target = Arr::set($target, $key2, $row);
            }
        }

        return $target;
    }

    /**
     * Pivot $origin['prefix']['xxx'] to $target['prefix_xxx'].
     *
     * @param   string $prefix A prefix text.
     * @param   array  $origin Origin array to pivot.
     * @param   array  $target A target array to store pivoted value.
     *
     * @return  array  Pivoted array.
     */
    public static function extractPrefix(string $prefix, $origin, $target = null)
    {
        $target = is_object($target) ? (object) $target : (array) $target;

        foreach ((array) $origin as $key => $val) {
            $key = $prefix . $key;
            if (!Arr::get($target, $key)) {
                $target = Arr::set($target, $key, $val);
            }
        }

        return $target;
    }

    /**
     * Pivot two-dimensional array to one-dimensional.
     *
     * @param   array &$array A two-dimension array.
     *
     * @return  array  Pivoted array.
     */
    public static function pivotFromTwoDimension(&$array)
    {
        foreach ((array) $array as $val) {
            if (is_array($val) || is_object($val)) {
                foreach ((array) $val as $key => $val2) {
                    $array = Arr::set($array, $key, $val2);
                }
            }
        }

        return $array;
    }

    /**
     * Pivot one-dimensional array to two-dimensional array by a key list.
     *
     * @param   array|object &$array Array to pivot.
     * @param   array         $keys  The fields' key list.
     *
     * @return  array  Pivoted array.
     */
    public static function pivotToTwoDimension(&$array, array $keys = [])
    {
        foreach ($keys as $key) {
            if (is_object($array)) {
                $array2 = clone $array;
            } else {
                $array2 = $array;
            }

            $array = Arr::set($array, $key, $array2);
        }

        return $array;
    }

    /**
     * filterRecursive
     *
     * @param  array    $array
     * @param  callable $callback
     *
     * @return  array
     */
    public static function filterRecursive($array, callable $callback): array
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = static::filterRecursive($value, $callback);
            }
        }

        return array_filter($array, $callback);
    }
}
