<?php

declare(strict_types=1);

namespace Windwalker\Session\Test\Cookie;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Session\Cookie\ArrayCookies;
use Windwalker\Session\Cookie\CookiesInterface;
use Windwalker\Session\Cookie\CookiesOptions;

class ArrayCookiesTest extends TestCase
{
    #[Test]
    public function createBuildsInstanceWithStorageAndOptions(): void
    {
        $cookies = ArrayCookies::create(['a' => '1'], ['path' => '/app']);

        self::assertSame('1', $cookies->get('a'));
        self::assertSame('/app', $cookies->getOptions()->path);
        self::assertSame(CookiesInterface::SAMESITE_LAX, $cookies->getOptions()->sameSite);
    }

    #[Test]
    public function getReturnsNullForMissingOrDeletedCookie(): void
    {
        $cookies = new ArrayCookies(['a' => '1']);

        self::assertNull($cookies->get('missing'));

        $cookies->remove('a');

        self::assertNull($cookies->get('a'));
    }

    #[Test]
    public function setStoresValueAndOverridesExisting(): void
    {
        $cookies = new ArrayCookies(['a' => '1']);

        self::assertTrue($cookies->set('a', '2'));
        self::assertTrue($cookies->set('b', '3'));

        self::assertSame(['a' => '2', 'b' => '3'], $cookies->getStorage());
    }

    #[Test]
    public function removeIgnoresMissingCookie(): void
    {
        $cookies = new ArrayCookies();

        self::assertTrue($cookies->remove('missing'));
        self::assertSame([], $cookies->getCookieHeaders());
    }

    #[Test]
    public function setStorageReplacesAllCookies(): void
    {
        $cookies = new ArrayCookies(['a' => '1']);

        self::assertSame($cookies, $cookies->setStorage(['x' => 'y']));
        self::assertSame(['x' => 'y'], $cookies->getStorage());
    }

    #[Test]
    public function getCookieHeadersOnlyIncludesModifiedAndDeletedCookies(): void
    {
        $cookies = new ArrayCookies(['untouched' => '1', 'old' => '2'], ['path' => '/']);
        $cookies->set('new', 'v', new CookiesOptions(secure: true, httpOnly: true, domain: 'example.com'));
        $cookies->remove('old');

        $headers = $cookies->getCookieHeaders();

        self::assertCount(2, $headers);
        self::assertSame('old=""; path=/; Max-Age=0; SameSite=Lax', $headers[0]);
        self::assertSame('new=v; domain=example.com; path=/; secure; SameSite=Lax; HttpOnly', $headers[1]);
    }

    #[Test]
    public function getCookieHeadersIncludesExpiresWhenConfigured(): void
    {
        $cookies = new ArrayCookies([], ['expires' => '2030-01-01 00:00:00 UTC']);
        $cookies->set('a', 'b');

        self::assertStringContainsString('Expires=Tuesday, 01-Jan-2030', $cookies->getCookieHeaders()[0]);
    }
}
