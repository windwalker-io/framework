<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Exception;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Http\Exception\HttpRequestException;
use Windwalker\Http\Response\Response;
use Windwalker\Stream\Stream;

class HttpRequestExceptionTest extends TestCase
{
    #[Test]
    public function withResponseReturnsNewExceptionKeepingMessageCodeAndPrevious(): void
    {
        $e = new HttpRequestException('Boom', 502);
        $response = new Response('php://memory', 502);

        $new = $e->withResponse($response);

        self::assertNotSame($e, $new);
        self::assertSame('Boom', $new->getMessage());
        self::assertSame(502, $new->getCode());
        self::assertSame($e, $new->getPrevious());
        self::assertSame($response, $new->response);
        self::assertNull($e->response);
    }

    #[Test]
    public function bodyAndBodyStringAreEmptyWithoutResponse(): void
    {
        $e = new HttpRequestException('Boom');

        self::assertNull($e->body);
        self::assertSame('', $e->getBodyString());
    }

    #[Test]
    public function bodyStringRewindsSeekableBodyBeforeReading(): void
    {
        $stream = new Stream('php://memory', 'rb+');
        $stream->write('error body');

        $e = (new HttpRequestException('Boom'))->withResponse(new Response($stream));

        self::assertSame($stream, $e->body);
        self::assertSame('error body', $e->getBodyString());
        self::assertSame('error body', $e->getBodyString());
    }

    #[Test]
    public function toCurlCmdInvokesConfiguredCallback(): void
    {
        $e = new HttpRequestException('Boom');

        self::assertSame($e, $e->setCurlCmdCallback(static fn() => 'curl https://example.com'));
        self::assertSame('curl https://example.com', $e->toCurlCmd());
    }

    #[Test]
    public function withResponseKeepsCurlCmdCallback(): void
    {
        $e = (new HttpRequestException('Boom'))
            ->setCurlCmdCallback(static fn() => 'curl https://example.com');

        $new = $e->withResponse(new Response());

        self::assertSame('curl https://example.com', $new->toCurlCmd());
    }
}
