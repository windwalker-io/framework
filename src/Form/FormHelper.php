<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Form;

/**
 * The FormHelper class.
 *
 * @since  2.0
 */
class FormHelper
{
    /**
     * encode
     *
     * @param string $html
     *
     * @return  string
     */
    public static function encode($html)
    {
        return htmlentities($html);
    }

    /**
     * Dump to on dimension array.
     *
     * @param array  $data      The data to convert.
     * @param string $separator The key separator.
     *
     * @return  string[] Dumped array.
     */
    public static function flatten($data, $separator = '.')
    {
        $array = [];

        static::toFlatten($separator, $data, $array);

        return $array;
    }

    /**
     * Method to recursively convert data to one dimension array.
     *
     * @param string       $separator The key separator.
     * @param array|object $data      Data source of this scope.
     * @param array        &$array    The result array, it is pass by reference.
     * @param string       $prefix    Last level key prefix.
     *
     * @return  void
     */
    protected static function toFlatten($separator = '_', $data = null, &$array = [], $prefix = '')
    {
        $data = (array)$data;

        foreach ($data as $k => $v) {
            $key = $prefix ? $prefix . $separator . $k : $k;

            if (is_object($v) || is_array($v)) {
                static::toFlatten($separator, $v, $array, $key);
            } else {
                $array[$key] = $v;
            }
        }
    }

    /**
     * Get data from array or object by path.
     *
     * Example: `ArrayHelper::getByPath($array, 'foo.bar.yoo')` equals to $array['foo']['bar']['yoo'].
     *
     * @param mixed  $data      An array or object to get value.
     * @param mixed  $paths     The key path.
     * @param string $separator Separator of paths.
     *
     * @return  mixed Found value, null if not exists.
     */
    public static function getByPath($data, $paths, $separator = '/')
    {
        if (empty($paths)) {
            return null;
        }

        $args = is_array($paths) ? $paths : explode($separator, $paths);

        $dataTmp = $data;

        foreach ($args as $arg) {
            if (is_object($dataTmp) && isset($dataTmp->$arg)) {
                $dataTmp = $dataTmp->$arg;
            } elseif (is_array($dataTmp) && isset($dataTmp[$arg])) {
                $dataTmp = $dataTmp[$arg];
            } else {
                return null;
            }
        }

        return $dataTmp;
    }

    /**
     * setByPath
     *
     * @param mixed  &$data
     * @param string $paths
     * @param mixed  $value
     * @param string $separator
     * @param string $type
     *
     * @return  boolean
     *
     * @since   2.0
     */
    public static function setByPath(&$data, $paths, $value, $separator = '/', $type = 'array')
    {
        if (empty($paths)) {
            return false;
        }

        $args = is_array($paths) ? $paths : explode($separator, $paths);

        /**
         * A closure as inner function to create data store.
         *
         * @param $type
         *
         * @return  array
         *
         * @throws \InvalidArgumentException
         */
        $createStore = function ($type) {
            if (strtolower($type) === 'array') {
                return [];
            }

            if (class_exists($type)) {
                return new $type;
            }

            throw new \InvalidArgumentException(sprintf('Type %s not supported of class not exists', $type));
        };

        $dataTmp = &$data;

        foreach ($args as $arg) {
            if (is_object($dataTmp)) {
                if (empty($dataTmp->$arg)) {
                    $dataTmp->$arg = $createStore($type);
                }

                $dataTmp = &$dataTmp->$arg;
            } elseif (is_array($dataTmp)) {
                if (empty($dataTmp[$arg])) {
                    $dataTmp[$arg] = $createStore($type);
                }

                $dataTmp = &$dataTmp[$arg];
            } else {
                $dataTmp = &$createStore($type);
            }
        }

        $dataTmp = $value;

        return true;
    }
}
