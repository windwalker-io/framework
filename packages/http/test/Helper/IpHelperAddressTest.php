<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Helper;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Throwable;
use Windwalker\Http\Helper\HttpHelper;
use Windwalker\Http\Helper\IpHelper;

class IpHelperAddressTest extends TestCase
{
    #[Test]
    public function detectsIpVersions(): void
    {
        self::assertTrue(IpHelper::isIp('127.0.0.1'));
        self::assertTrue(IpHelper::isIp('::1'));
        self::assertFalse(IpHelper::isIp('example.com'));
        self::assertTrue(IpHelper::isIpv4('10.1.2.3'));
        self::assertFalse(IpHelper::isIpv4('::1'));
        self::assertTrue(IpHelper::isIpv6('2001:db8::1'));
        self::assertFalse(IpHelper::isIpv6('10.1.2.3'));
        self::assertTrue(HttpHelper::isIPv6('::1'));
        self::assertFalse(HttpHelper::isIPv6('1.1.1.1'));
    }

    #[Test]
    #[DataProvider('checkIpProvider')]
    public function checksIpAgainstAddressesAndSubnets(string $ip, string|array $ips, bool $expected): void
    {
        self::assertSame($expected, IpHelper::checkIp($ip, $ips));
    }

    public static function checkIpProvider(): array
    {
        return [
            'exact ipv4' => ['192.168.1.1', '192.168.1.1', true],
            'ipv4 subnet match' => ['192.168.1.77', '192.168.1.0/24', true],
            'ipv4 subnet miss' => ['192.168.2.1', '192.168.1.0/24', false],
            'ipv4 any with zero netmask' => ['8.8.8.8', '1.2.3.4/0', true],
            'ipv4 zero netmask invalid address' => ['8.8.8.8', 'foo/0', false],
            'ipv4 netmask too large' => ['192.168.1.1', '192.168.1.1/33', false],
            'ipv4 invalid candidate address' => ['192.168.1.1', 'not-an-ip', false],
            'ipv4 invalid request ip' => ['999.1.1.1', '999.1.1.1', false],
            'ipv4 in list' => ['10.0.0.5', ['1.1.1.1', '10.0.0.0/8'], true],
            'ipv4 not in list' => ['10.0.0.5', ['1.1.1.1', '172.16.0.0/12'], false],
            'exact ipv6' => ['2001:db8::1', '2001:db8::1', true],
            'ipv6 subnet match' => ['2001:db8::1234', '2001:db8::/32', true],
            'ipv6 subnet miss' => ['2001:db9::1', '2001:db8::/32', false],
            'ipv6 any with zero netmask' => ['2001:db8::1', '::1/0', true],
            'ipv6 zero netmask invalid address' => ['2001:db8::1', 'zzz/0', false],
            'ipv6 netmask out of range' => ['2001:db8::1', '2001:db8::/129', false],
            'ipv6 invalid candidate' => ['2001:db8::1', 'zzz', false],
            'ipv6 against ipv4 range' => ['2001:db8::1', '10.0.0.0/8', false],
            'empty list' => ['10.0.0.1', [], false],
        ];
    }

    #[Test]
    public function checkIpRejectsNonStringEntries(): void
    {
        $this->expectException(Throwable::class);

        IpHelper::checkIp('10.0.0.1', [123]);
    }

    #[Test]
    #[DataProvider('anonymizeProvider')]
    public function anonymizesAddresses(string $ip, string $expected): void
    {
        self::assertSame($expected, IpHelper::anonymize($ip));
    }

    public static function anonymizeProvider(): array
    {
        return [
            'ipv4' => ['192.168.1.123', '192.168.1.0'],
            'ipv6' => ['2a01:198:603:10:396e:4789:8e99:890f', '2a01:198:603:10::'],
            'wrapped ipv6' => ['[2a01:198:603:10:396e:4789:8e99:890f]', '[2a01:198:603:10::]'],
            'ipv4 mapped ipv6' => ['::ffff:123.234.235.236', '::ffff:123.234.235.0'],
            'short ipv6 low 48 bits' => ['::1', '::'],
        ];
    }

    #[Test]
    public function getTrustedIpReturnsRemoteAddressWhenNoForwardedHeader(): void
    {
        self::assertSame(
            '10.0.0.2',
            IpHelper::getTrustedIp(['10.0.0.2'], ['REMOTE_ADDR' => '10.0.0.2', 'HTTP_X_FORWARDED_FOR' => ' ']),
        );
    }

    #[Test]
    public function getTrustedIpReturnsRemoteAddressWhenWholeChainIsTrusted(): void
    {
        $server = ['REMOTE_ADDR' => '10.0.0.2', 'HTTP_X_FORWARDED_FOR' => '10.0.0.3, 10.0.0.4'];

        self::assertSame('10.0.0.2', IpHelper::getTrustedIp(['10.0.0.0/8'], $server));
    }

    #[Test]
    public function getTrustedIpReturnsInvalidRemoteAddressAsIs(): void
    {
        self::assertSame('garbage', IpHelper::getTrustedIp(['10.0.0.0/8'], ['REMOTE_ADDR' => 'garbage']));
    }

    #[Test]
    public function getTrustedIpFallsBackToGlobalServer(): void
    {
        $original = $_SERVER;
        $_SERVER['REMOTE_ADDR'] = '203.0.113.9';

        try {
            self::assertSame('203.0.113.9', IpHelper::getTrustedIp());
        } finally {
            $_SERVER = $original;
        }
    }
}
