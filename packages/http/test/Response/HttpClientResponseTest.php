<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Response;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Http\Request\Request;
use Windwalker\Http\Response\HttpClientResponse;
use Windwalker\Http\Response\Response;
use Windwalker\Stream\Stream;

class HttpClientResponseTest extends TestCase
{
    #[Test]
    public function fromHttpClientCopiesPsrResponseAndKeepsRequest(): void
    {
        $request = new Request('http://example.com', 'GET');
        $psr = Response::fromString('Body', 201, ['X-Foo' => 'bar'])->withStatus(201, 'Made');

        $response = HttpClientResponse::fromHttpClient($request, $psr, fn() => 'curl cmd');

        self::assertSame($request, $response->request);
        self::assertSame(201, $response->getStatusCode());
        self::assertSame('Made', $response->getReasonPhrase());
        self::assertSame('bar', $response->getHeaderLine('X-Foo'));
        self::assertSame('Body', $response->getContent());
        self::assertSame('curl cmd', $response->toCurlCmd());
    }

    #[Test]
    public function fromHttpClientReusesInstanceOfSameClass(): void
    {
        $request = new Request('http://example.com', 'GET');
        $inner = HttpClientResponse::fromHttpClient($request, new Response(), fn() => 'a');

        $outer = HttpClientResponse::fromHttpClient($request, $inner, fn() => 'b');

        self::assertSame($inner, $outer);
        self::assertSame('b', $outer->toCurlCmd());
    }

    #[Test]
    public function withInfoReturnsNewInstanceWithInfo(): void
    {
        $response = new HttpClientResponse();

        $new = $response->withInfo(['http_code' => 200]);

        self::assertNotSame($response, $new);
        self::assertSame(['http_code' => 200], $new->getInfo());
        self::assertSame(['http_code' => 200], $new->info);
        self::assertNull($response->getInfo());
    }

    #[Test]
    public function getBodyStringRewindsBodyBeforeReading(): void
    {
        $stream = new Stream('php://temp', 'wb+');
        $stream->write('Hello');
        $response = new HttpClientResponse($stream);

        self::assertSame('Hello', $response->getBodyString());
        self::assertSame('Hello', $response->getBodyString());
    }
}
