<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Transport;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Windwalker\Http\Exception\HttpRequestException;
use Windwalker\Http\Request\Request;
use Windwalker\Http\Response\Response;
use Windwalker\Http\Transport\CurlTransport;
use Windwalker\Http\Transport\Options\CurlOptions;
use Windwalker\Http\Transport\ProgressEvent;

class CurlTransportUnitTest extends TestCase
{
    protected function prepare(Request $request, CurlOptions|array $options = []): array
    {
        $transport = new CurlTransport();
        $ref = new ReflectionMethod($transport, 'prepareCurlOptions');

        return $ref->invoke($transport, $request, CurlOptions::wrap($options));
    }

    #[Test]
    public function injectHeadersToResponseSetsStatusAndHeadersFromString(): void
    {
        $res = new CurlTransport()->injectHeadersToResponse(
            new Response(),
            "HTTP/1.1 201 Created\r\nX-Foo: bar",
        );

        self::assertSame(201, $res->getStatusCode());
        self::assertSame('bar', $res->getHeaderLine('X-Foo'));
    }

    #[Test]
    public function injectHeadersToResponseThrowsWhenNoStatusCodeFound(): void
    {
        $this->expectException(HttpRequestException::class);

        new CurlTransport()->injectHeadersToResponse(new Response(), ['garbage']);
    }

    #[Test]
    public function injectHeadersToResponseAllowsEmptyStatusCodeWhenRequested(): void
    {
        $res = new CurlTransport()->injectHeadersToResponse(
            new Response(),
            ['garbage', 'X-A: 1'],
            true,
        );

        self::assertSame(200, $res->getStatusCode());
        self::assertSame('1', $res->getHeaderLine('X-A'));
    }

    #[Test]
    public function contentToResponseUsesLastHeaderBlockAfterRedirects(): void
    {
        $content = "HTTP/1.1 302 Found\r\nLocation: /b\r\n\r\n"
            . "HTTP/1.1 200 OK\r\nX-Final: yes\r\n\r\nthe body";

        $res = new CurlTransport()->contentToResponse($content, ['redirect_count' => 1]);

        self::assertSame(200, $res->getStatusCode());
        self::assertSame('yes', $res->getHeaderLine('X-Final'));
        self::assertSame('the body', (string) $res->getBody());
    }

    #[Test]
    public function getResponseBuildsResponseFromRawContent(): void
    {
        $res = new CurlTransport()->getResponse("HTTP/1.1 404 Not Found\r\nX-A: b\r\n\r\nmissing", []);

        self::assertSame(404, $res->getStatusCode());
        self::assertSame('missing', (string) $res->getBody());
    }

    #[Test]
    #[DataProvider('protocolVersionProvider')]
    public function prepareCurlOptionsMapsProtocolVersion(string $version, int $expected): void
    {
        $opt = $this->prepare(new Request()->withProtocolVersion($version));

        self::assertSame($expected, $opt[CURLOPT_HTTP_VERSION]);
    }

    public static function protocolVersionProvider(): array
    {
        return [
            'http 1.0' => ['1.0', CURL_HTTP_VERSION_1_0],
            'http 1.1' => ['1.1', CURL_HTTP_VERSION_1_1],
            'http 2' => ['2', CURL_HTTP_VERSION_2_0],
        ];
    }

    #[Test]
    public function prepareCurlOptionsAddsProgressCallbackWhenConfigured(): void
    {
        $events = [];

        $options = new CurlOptions();
        $options->progress = function (ProgressEvent $e) use (&$events) {
            $events[] = $e;

            return 0;
        };

        $opt = $this->prepare(new Request(), $options);

        self::assertFalse($opt[CURLOPT_NOPROGRESS]);

        $ch = curl_init();
        $result = $opt[CURLOPT_XFERINFOFUNCTION]($ch, 10.0, 5.0, 2.0, 1.0);

        self::assertSame(0, $result);
        self::assertCount(1, $events);
        self::assertSame(10.0, $events[0]->downloadTotal);
        self::assertSame(5.0, $events[0]->downloaded);
        self::assertSame(2.0, $events[0]->uploadTotal);
        self::assertSame(1.0, $events[0]->uploaded);
    }

    #[Test]
    public function prepareCurlOptionsUsesCertPathAsCaInfo(): void
    {
        $opt = $this->prepare(new Request(), ['certpath' => '/path/to/cacert.pem']);

        self::assertSame('/path/to/cacert.pem', $opt[CURLOPT_CAINFO]);
    }

    #[Test]
    public function downloadThrowsWhenDestinationIsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new CurlTransport()->download(new Request(), '');
    }

    #[Test]
    public function isSupportedReflectsCurlExtension(): void
    {
        self::assertSame(function_exists('curl_init'), CurlTransport::isSupported());
    }
}
