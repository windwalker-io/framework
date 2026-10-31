<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Response;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Http\Response\TextResponse;
use Windwalker\Stream\Stream;

class TextResponseTest extends TestCase
{
    #[Test]
    public function constructWritesStringIntoRewoundBody(): void
    {
        $response = new TextResponse('Hello', 201, ['X-Foo' => 'bar']);

        self::assertSame('Hello', (string) $response->getBody());
        self::assertSame(201, $response->getStatusCode());
        self::assertSame('bar', $response->getHeaderLine('X-Foo'));
        self::assertSame('text/plain; charset=utf-8', $response->getHeaderLine('Content-Type'));
    }

    #[Test]
    public function constructKeepsStreamInstance(): void
    {
        $stream = Stream::fromString('Stream');

        $response = new TextResponse($stream);

        self::assertSame($stream, $response->getBody());
    }

    #[Test]
    public function constructThrowsOnUnsupportedBodyType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('Invalid body content type array');

        new TextResponse(['foo']);
    }

    #[Test]
    public function withContentReplacesBody(): void
    {
        $response = new TextResponse('Old');

        $new = $response->withContent('New');

        self::assertNotSame($response, $new);
        self::assertSame('New', (string) $new->getBody());
        self::assertSame('Old', (string) $response->getBody());
    }
}
