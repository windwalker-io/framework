<?php
/**
 * Part of ww4 project.
 *
 * @copyright  Copyright (C) 2016 LYRASOFT.
 * @license    Please see LICENSE file.
 */
declare(strict_types=1);

namespace Windwalker\Utilities;

/**
 * The ArrayHelper class
 *
 * @since  2.0
 */
class ArrayHelper
{
    /**
     * Check a key exists in object or array. The key can be a path separated by dots.
     *
     * @param array|object $array     Object or array to check.
     * @param string       $key       The key path name.
     * @param string       $separator The separator to split paths.
     *
     * @return  bool
     *
     * @since 4.0
     */
    public static function has($array, string $key, string $separator = '.') : bool
    {
        $nodes = array_values(array_filter(explode($separator, $key), 'strlen'));

        if (empty($nodes)) {
            return false;
        }

        $dataTmp = $array;

        foreach ($nodes as $arg) {
            if (is_object($dataTmp) && property_exists($dataTmp, $arg)) {
                // Check object value exists
                $dataTmp = $dataTmp->$arg;
            } elseif ($dataTmp instanceof \ArrayAccess && isset($dataTmp[$arg])) {
                // Check arrayAccess value exists
                $dataTmp = $dataTmp[$arg];
            } elseif (is_array($dataTmp) && array_key_exists($arg, $dataTmp)) {
                // Check object value exists
                $dataTmp = $dataTmp[$arg];
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Set a default value to array or object if not exists. Key can be a path separated by dots.
     *
     * @param array|object $array     Object or array to set default value.
     * @param string       $key       Key path name.
     * @param mixed        $value     Value to set if not exists.
     * @param string       $separator Separator to split paths.
     *
     * @return  array|object
     * @throws \InvalidArgumentException
     *
     * @since 4.0
     */
    public static function def($array, string $key, $value, string $separator = '.')
    {
        if (static::has($array, $key, $separator)) {
            return $array;
        }

        static::set($array, $key, $value, $separator);

        return $array;
    }

    /**
     * Get data from array or object by path.
     *
     * Example: `ArrayHelper::get($array, 'foo.bar.yoo')` equals to $array['foo']['bar']['yoo'].
     *
     * @param mixed  $data      An array or object to get value.
     * @param string $key       The key path.
     * @param mixed  $default   The default value if not exists.
     * @param string $separator Separator of paths.
     *
     * @return mixed Found value, null if not exists.
     *
     * @since   2.0
     */
    public static function get($data, string $key, $default = null, string $separator = '.')
    {
        $nodes = array_values(array_filter(explode($separator, $key), 'strlen'));

        if (empty($nodes)) {
            return $default;
        }

        $dataTmp = $data;

        foreach ($nodes as $arg) {
            if (is_object($dataTmp) && isset($dataTmp->$arg)) {
                // Check object value exists
                $dataTmp = $dataTmp->$arg;
            } elseif ($dataTmp instanceof \ArrayAccess && isset($dataTmp[$arg])) {
                // Check arrayAccess value exists
                $dataTmp = $dataTmp[$arg];
            } elseif (is_array($dataTmp) && isset($dataTmp[$arg])) {
                // Check object value exists
                $dataTmp = $dataTmp[$arg];
            } else {
                return $default;
            }
        }

        return $dataTmp;
    }

    /**
     * Set value into array or object. The key can be path type.
     *
     * @param mixed  &$data     An array or object to set data.
     * @param string $key       Path name separate by dot.
     * @param mixed  $value     Value to set into array or object.
     * @param string $separator Separator to split path.
     * @param string $storeType The new store data type, default is `array`. you can set object class name.
     *
     * @return  boolean
     * @throws \InvalidArgumentException
     *
     * @since   2.0
     */
    public static function set(&$data, string $key, $value, string $separator = '.', string $storeType = 'array') : bool
    {
        $nodes = array_values(array_filter(explode($separator, $key), 'strlen'));

        if (empty($nodes)) {
            return false;
        }

        /**
         * A closure as inner function to create data store.
         *
         * @param string $type Type name.
         *
         * @return  array
         *
         * @throws \InvalidArgumentException
         */
        $createStore = function (string $type) {
            if (strtolower($type) === 'array') {
                return array();
            }

            if (class_exists($type)) {
                return new $type;
            }

            throw new \InvalidArgumentException(sprintf('Type or class: %s not exists', $type));
        };

        $dataTmp = &$data;

        foreach ($nodes as $node) {
            if (is_object($dataTmp)) {
                if (empty($dataTmp->$node)) {
                    $dataTmp->$node = $createStore($storeType);
                }

                $dataTmp = &$dataTmp->$node;
            } elseif (is_array($dataTmp)) {
                if (empty($dataTmp[$node])) {
                    $dataTmp[$node] = $createStore($storeType);
                }

                $dataTmp = &$dataTmp[$node];
            } else {
                // If a node is value but path is not go to the end, we replace this value as a new store.
                // Then next node can insert new value to this store.
                $dataTmp = &$createStore($storeType);
            }
        }

        // Now, path go to the end, means we get latest node, set value to this node.
        $dataTmp = $value;

        return true;
    }

    /**
     * Remove a value from array or object. The key can be a path separated by dots.
     *
     * @param array|object &$data     Object or array to remove value.
     * @param string       $key       The key path name.
     * @param string       $separator The separator to split paths.
     *
     * @return  bool
     */
    public static function remove(&$data, string $key, string $separator = '.') : bool
    {
        $nodes = array_values(array_filter(explode($separator, $key), 'strlen'));

        if (!count($nodes)) {
            return false;
        }

        $previous = null;
        $dataTmp  = &$data;

        foreach ($nodes as $node) {
            if (is_object($dataTmp)) {
                if (empty($dataTmp->$node)) {
                    return false;
                }

                $previous = &$dataTmp;
                $dataTmp  = &$dataTmp->$node;
            } elseif (is_array($dataTmp)) {
                if (empty($dataTmp[$node])) {
                    return false;
                }

                $previous = &$dataTmp;
                $dataTmp  = &$dataTmp[$node];
            } else {
                return false;
            }
        }

        // Now, path go to the end, means we get latest node, set value to this node.
        if (is_object($previous)) {
            unset($previous->$node);
        } elseif (is_array($previous)) {
            unset($previous[$node]);
        }

        return true;
    }
}
