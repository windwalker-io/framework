<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Environment;

/**
 * The ServerHelper class.
 *
 * @since  2.0
 *
 * @deprecated Use Environment instead.
 */
class PlatformHelper
{
    protected static Environment $environment;

    /**
     * isWindows
     *
     * @return  boolean
     */
    public static function isWindows(): bool
    {
        return static::getEnvironment()->isWindows();
    }

    /**
     * isLinux
     *
     * @return  boolean
     */
    public static function isLinux(): bool
    {
        return static::getEnvironment()->isLinux();
    }

    /**
     * isUnix
     *
     * @return  boolean
     */
    public static function isUnix(): bool
    {
        return static::getEnvironment()->isUnix();
    }

    /**
     * @return  Environment
     */
    public static function getEnvironment(): Environment
    {
        return static::$environment ??= new Environment();
    }
}
