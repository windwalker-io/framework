<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Response;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Http\Response\RedirectResponse;
use Windwalker\Http\Test\Stub\HeadersSentSwitch;
use Windwalker\Uri\Uri;

require_once __DIR__ . '/../Stub/headers_sent_override.php';

class RedirectResponseTest extends TestCase
{
    #[After]
    protected function resetHeadersSent(): void
    {
        HeadersSentSwitch::$forced = null;
    }

    #[Test]
    public function constructDefaultsToSeeOtherWithoutLocation(): void
    {
        $response = new RedirectResponse();

        self::assertSame(303, $response->getStatusCode());
        self::assertFalse($response->hasHeader('location'));
        self::assertSame('', $response->getLocation());
    }

    #[Test]
    public function constructAcceptsStringableUri(): void
    {
        $response = new RedirectResponse(new Uri('http://example.com/foo?bar=1'), 302, ['X-Foo' => 'bar']);

        self::assertSame('http://example.com/foo?bar=1', $response->getLocation());
        self::assertSame(302, $response->getStatusCode());
        self::assertSame('bar', $response->getHeaderLine('X-Foo'));
    }

    #[Test]
    #[DataProvider('unsafeLocationProvider')]
    public function constructStripsLineBreaksFromLocation(string $uri, string $expected): void
    {
        $response = new RedirectResponse($uri);

        self::assertSame($expected, $response->getLocation());
    }

    public static function unsafeLocationProvider(): array
    {
        return [
            'CRLF injection' => ["/foo\r\nSet-Cookie: a=b", '/fooSet-Cookie: a=b'],
            'LF only' => ["/foo\nbar", '/foobar'],
            'clean' => ['/foo', '/foo'],
        ];
    }

    #[Test]
    public function getBodyRendersEscapedScriptRedirectWhenHeadersAlreadySent(): void
    {
        HeadersSentSwitch::$forced = true;

        $response = new RedirectResponse('http://example.com/a?b=1&c=2');

        self::assertSame(
            "<script>document.location.href=\"http:\\/\\/example.com\\/a?b=1\\u0026c=2\";</script>\n",
            (string) $response->getBody(),
        );
    }

    #[Test]
    public function getBodyReturnsEmptyBodyWhenHeadersNotSent(): void
    {
        HeadersSentSwitch::$forced = false;

        $response = new RedirectResponse('http://example.com');

        self::assertSame('', (string) $response->getBody());
    }

    #[Test]
    #[DataProvider('unsafeSchemeProvider')]
    public function getBodyDoesNotRenderScriptForNonHttpSchemes(string $location): void
    {
        HeadersSentSwitch::$forced = true;

        $response = new RedirectResponse($location);

        self::assertSame('', (string) $response->getBody());
    }

    public static function unsafeSchemeProvider(): array
    {
        return [
            'javascript' => ['javascript:alert(1)'],
            'data' => ['data:text/html,hi'],
            'malformed' => ['http:///example.com'],
        ];
    }

    #[Test]
    public function getBodyRendersScriptForRelativeLocation(): void
    {
        HeadersSentSwitch::$forced = true;

        $response = new RedirectResponse('/foo/bar');

        self::assertStringContainsString('"\/foo\/bar"', (string) $response->getBody());
    }
}
