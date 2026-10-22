<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Helper;

use PHPUnit\Framework\TestCase;
use Windwalker\Http\Helper\IpHelper;

class IpHelperTest extends TestCase
{
    public function testIgnoresForwardingHeadersFromUntrustedPeers(): void
    {
        $server = [
            'REMOTE_ADDR' => '203.0.113.10',
            'HTTP_CLIENT_IP' => '198.51.100.20',
            'HTTP_X_FORWARDED_FOR' => '198.51.100.30',
        ];

        self::assertSame('203.0.113.10', IpHelper::getTrustedIp(['10.0.0.1'], $server));
    }

    public function testFindsFirstUntrustedAddressFromTrustedProxyChain(): void
    {
        $server = [
            'REMOTE_ADDR' => '10.0.0.2',
            'HTTP_X_FORWARDED_FOR' => '198.51.100.30, 10.0.0.1',
        ];

        self::assertSame('198.51.100.30', IpHelper::getTrustedIp(['10.0.0.1', '10.0.0.2'], $server));
    }

    public function testAcceptsTrustedIpv4CidrRanges(): void
    {
        $server = [
            'REMOTE_ADDR' => '10.0.0.127',
            'HTTP_X_FORWARDED_FOR' => '198.51.100.30',
        ];

        self::assertSame('198.51.100.30', IpHelper::getTrustedIp(['10.0.0.0/25'], $server));
        self::assertSame('10.0.0.127', IpHelper::getTrustedIp(['10.0.1.0/24'], $server));
        self::assertSame('10.0.0.127', IpHelper::getTrustedIp(['10.0.0.0/33'], $server));
    }

    public function testAcceptsTrustedIpv6CidrRanges(): void
    {
        $server = [
            'REMOTE_ADDR' => '2001:db8:1:0:8000::2',
            'HTTP_X_FORWARDED_FOR' => '198.51.100.30',
        ];

        self::assertSame(
            '198.51.100.30',
            IpHelper::getTrustedIp(['2001:db8:1:0:8000::/65'], $server)
        );
    }

    public function testFallsBackToRemoteAddressWhenForwardedChainIsInvalid(): void
    {
        $server = [
            'REMOTE_ADDR' => '10.0.0.2',
            'HTTP_X_FORWARDED_FOR' => '198.51.100.30, invalid',
        ];

        self::assertSame('10.0.0.2', IpHelper::getTrustedIp(['10.0.0.2'], $server));
    }
}
