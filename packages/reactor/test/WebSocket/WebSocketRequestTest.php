<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Test\WebSocket;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Reactor\WebSocket\WebSocketFrame;
use Windwalker\Reactor\WebSocket\WebSocketRequest;

class WebSocketRequestTest extends TestCase
{
    #[Test]
    public function createFromFrameExposesFrameFdAndData(): void
    {
        $frame = new WebSocketFrame(7, 'payload');
        $request = WebSocketRequest::createFromFrame($frame);

        self::assertSame($frame, $request->getFrame());
        self::assertSame(7, $request->getFd());
        self::assertSame('payload', $request->getData());
    }

    #[Test]
    public function withFrameDoesNotMutateOriginalRequest(): void
    {
        $a = WebSocketRequest::createFromFrame(new WebSocketFrame(1, 'a'));
        $b = $a->withFrame(new WebSocketFrame(2, 'b'));

        self::assertSame(1, $a->getFd());
        self::assertSame(2, $b->getFd());
    }

    #[Test]
    public function getReadsQueryParamsAndReturnsNullWhenMissing(): void
    {
        $request = WebSocketRequest::createFromFrame(new WebSocketFrame(1))
            ->withQueryParams(['token' => 'abc']);

        self::assertSame('abc', $request->get('token'));
        self::assertNull($request->get('none'));
    }

    #[Test]
    public function withAttributesReplacesAllAttributes(): void
    {
        $request = WebSocketRequest::createFromFrame(new WebSocketFrame(1))
            ->withAttribute('old', 1);

        $new = $request->withAttributes(['foo' => 'bar']);

        self::assertSame(['foo' => 'bar'], $new->getAttributes());
        self::assertSame(['old' => 1], $request->getAttributes());
    }

    #[Test]
    public function withParsedDataIsImmutable(): void
    {
        $request = WebSocketRequest::createFromFrame(new WebSocketFrame(1));
        $new = $request->withParsedData(['a' => 1]);

        self::assertNull($request->getParsedData());
        self::assertSame(['a' => 1], $new->getParsedData());
    }
}
