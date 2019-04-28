<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT Taiwan, Inc.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Utilities\Classes;

/**
 * The MultiSingletonTrait class.
 *
 * @since  2.0
 */
trait MultiSingletonTrait
{
    /**
     * Property instances.
     *
     * @var  array
     */
    protected static $instances = [];

    /**
     * getInstance
     *
     * @param string $name
     *
     * @return  static
     */
    public static function getInstance($name)
    {
        if (!empty(static::$instances[$name])) {
            return static::$instances[$name];
        }

        return null;
    }

    /**
     * setInstance
     *
     * @param string $name
     * @param object $instance
     *
     * @return  mixed
     */
    protected static function setInstance($name, $instance)
    {
        return static::$instances[$name] = $instance;
    }

    /**
     * hasInstance
     *
     * @param string $name
     *
     * @return  bool
     */
    protected static function hasInstance($name)
    {
        return isset(static::$instances[$name]);
    }
}
