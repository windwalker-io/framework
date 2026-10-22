<?php

declare(strict_types=1);

namespace Windwalker\Http\Helper;

use JetBrains\PhpStorm\Pure;

/**
 * The HttpHelper class.
 *
 * @since  3.5.13
 *
 * @deprecated Use {@see IpHelper} instead.
 */
class HttpHelper
{
    /**
     * @deprecated Use {@see IpHelper::getTrustedIp()} and configure trusted proxies explicitly.
     */
    public static function getIp(array $server = []): string
    {
        return IpHelper::getTrustedIp(server: $server);
    }

    /**
     * @param  string  $ip
     *
     * @return  bool
     *
     * @since  3.5.15
     *
     * @deprecated Use {@see IpHelper::isIPv6()} instead.
     */
    #[Pure]
    public static function isIPv6(string $ip): bool
    {
        return IpHelper::isIPv6($ip);
    }
}
