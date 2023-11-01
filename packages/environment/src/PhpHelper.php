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
 * The PhpEnvironment class.
 *
 * @since  2.0
 */
class PhpHelper
{
    /**
     * @return  bool
     */
    public static function isWebServer(): bool
    {
        return in_array(
            PHP_SAPI,
            [
                'apache',
                'cgi',
                'fast-cgi',
                'srv',
            ]
        );
    }

    /**
     * @return  bool
     */
    public static function isCli(): bool
    {
        return in_array(
            PHP_SAPI,
            [
                'cli',
                'cli-server',
            ]
        );
    }

    /**
     * @return  bool
     */
    public static function isHHVM(): bool
    {
        return defined('HHVM_VERSION');
    }

    /**
     * @return  bool
     */
    public static function isPHP(): bool
    {
        return !static::isHHVM();
    }

    /**
     * isEmbed
     *
     * @return  bool
     */
    public static function isEmbed(): bool
    {
        return PHP_SAPI === 'embed';
    }

    /**
     * Get PHP version
     *
     * @return string
     */
    public function getVersion(): string
    {
        return PHP_VERSION;
    }

    /**
     * setStrict
     *
     * @return  void
     */
    public static function setStrict(): void
    {
        error_reporting(-1);
    }

    /**
     * setMuted
     *
     * @return  void
     */
    public static function setMuted(): void
    {
        error_reporting(0);
    }

    /**
     * Returns true when the runtime used is PHP and Xdebug is loaded.
     *
     * @return bool
     */
    public function hasXdebug(): bool
    {
        return static::isPHP() && extension_loaded('xdebug');
    }

    /**
     * @return  bool
     */
    public static function hasPcntl(): bool
    {
        return extension_loaded('PCNTL');
    }

    /**
     * @return  bool
     */
    public static function hasCurl(): bool
    {
        return function_exists('curl_init');
    }

    /**
     * @return  bool
     */
    public static function hasMcrypt(): bool
    {
        return extension_loaded('mcrypt');
    }

    /**
     * @return  bool
     */
    public static function hasOpenssl(): bool
    {
        return extension_loaded('openssl');
    }
}
