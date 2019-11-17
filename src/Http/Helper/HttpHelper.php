<?php
/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2019 .
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Http\Helper;

/**
 * The HttpHelper class.
 *
 * @since  3.5.13
 */
class HttpHelper
{
    /**
     * getIp
     *
     * @see https://www.phpini.com/php/php-get-real-ip
     *
     * @param  array  $server
     *
     * @return  string
     *
     * @since  3.5.13
     */
    public static function getIp(array $server = []): string
    {
        $server = $server ?: $_SERVER;

        if (!empty($server['HTTP_CLIENT_IP'])) {
            return $server['HTTP_CLIENT_IP'];
        }

        if (!empty($server['HTTP_X_FORWARDED_FOR'])) {
            return $server['HTTP_X_FORWARDED_FOR'];
        }

        return $server['REMOTE_ADDR'];
    }

    /**
     * isIPv6
     *
     * @param  string  $ip
     *
     * @return  bool
     *
     * @since  3.5.15
     */
    public static function isIPv6(string $ip): bool
    {
        return strpos($ip, ':') !== false;
    }
}
