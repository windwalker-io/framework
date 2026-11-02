<?php

declare(strict_types=1);

namespace Windwalker\Session\Test\Cookie;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Session\Cookie\CookiesOptions;

class CookiesOptionsTest extends TestCase
{
    #[Test]
    public function expiresAcceptsNullIntStringAndDateTime(): void
    {
        $options = new CookiesOptions();

        self::assertNull($options->expires);

        $options->expires = 1893456000;
        self::assertSame(1893456000, $options->expires->getTimestamp());

        $options->expires = '2030-01-01 00:00:00 UTC';
        self::assertSame(1893456000, $options->expires->getTimestamp());

        $options->expires = new \DateTimeImmutable('@1893456000');
        self::assertSame(1893456000, $options->expires->getTimestamp());

        $options->expires = new \DateTime('@1893456000');
        self::assertInstanceOf(\DateTimeImmutable::class, $options->expires);
        self::assertSame(1893456000, $options->expires->getTimestamp());

        self::assertSame(1893456000, new CookiesOptions(new \DateTime('@1893456000'))->expires->getTimestamp());

        $options->expires = null;
        self::assertNull($options->expires);
    }

    #[Test]
    public function wrapWithNormalizesLowercaseKeys(): void
    {
        $options = CookiesOptions::wrapWith(['httponly' => true, 'samesite' => 'Strict']);

        self::assertTrue($options->httpOnly);
        self::assertSame('Strict', $options->sameSite);
    }

    #[Test]
    public function toCookieParamsOmitsNullValues(): void
    {
        $options = new CookiesOptions(1893456000, '/', null, true, true, 'Lax');

        self::assertSame(
            [
                'expires' => 1893456000,
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Lax',
            ],
            $options->toCookieParams()
        );
        self::assertSame([], new CookiesOptions()->toCookieParams());
    }

    #[Test]
    public function toSessionCookieParamsConvertsExpiresToLifetime(): void
    {
        $options = new CookiesOptions(time() + 100, '/');
        $params = $options->toSessionCookieParams();

        self::assertEqualsWithDelta(100, $params['expires'], 2);
        self::assertSame('/', $params['path']);
        self::assertSame([], new CookiesOptions()->toSessionCookieParams());
    }
}
