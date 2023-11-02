<?php

/**
 * Part of framework project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Http;

use Composer\CaBundle\CaBundle;

/**
 * The CaBundleFinder class.
 */
class CaBundleFinder
{
    protected static ?string $defaultPath = null;

    protected static \Closure $finder;

    public static function find(): ?string
    {
        $file = static::getDefaultPath();

        if ($file && file_exists($file)) {
            return $file;
        }

        $finder = static::getFinder();

        $file =$finder();

        if (file_exists($file)) {
            return $file;
        }

        return null;
    }

    public static function getFinder(): \Closure
    {
        return self::$finder ??= static fn () => CaBundle::getBundledCaBundlePath();
    }

    /**
     * @param  \Closure  $finder
     *
     * @return  void
     */
    public static function setFinder(\Closure $finder): void
    {
        static::$finder = $finder;
    }

    public static function getDefaultPath(): ?string
    {
        return self::$defaultPath;
    }

    /**
     * @param  string|null  $defaultPath
     *
     * @return  void
     */
    public static function setDefaultPath(?string $defaultPath): void
    {
        static::$defaultPath = $defaultPath;
    }
}
