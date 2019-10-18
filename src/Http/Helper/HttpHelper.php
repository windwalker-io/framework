<?php
/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2019 .
 * @license    __LICENSE__
 */

namespace Windwalker\Http\Helper;

/**
 * The HttpHelper class.
 *
 * @since  __DEPLOY_VERSION__
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
     * @since  __DEPLOY_VERSION__
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
}
