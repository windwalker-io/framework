<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Environment;

/**
 * The ServerHelper class.
 *
 * @since  2.0
 */
class PlatformHelper
{
    /**
     * Property server.
     *
     * @var Platform
     */
    protected static $platform;

    /**
     * isWindows
     *
     * @return  boolean
     */
    public static function isWindows()
    {
        return static::getPlatform()->isWin();
    }

    /**
     * isLinux
     *
     * @return  boolean
     */
    public static function isLinux()
    {
        return static::getPlatform()->isLinux();
    }

    /**
     * isUnix
     *
     * @return  boolean
     */
    public static function isUnix()
    {
        return static::getPlatform()->isUnix();
    }

    /**
     * getServer
     *
     * @return  Platform
     */
    public static function getPlatform()
    {
        if (!static::$platform) {
            static::$platform = new Platform;
        }

        return static::$platform;
    }

    /**
     * Method to set property server
     *
     * @param   Platform $platform
     *
     * @return  void
     */
    public static function setPlatform($platform)
    {
        static::$platform = $platform;
    }
}
