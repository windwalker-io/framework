<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Test\WebSocket;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Reactor\WebSocket\WebSocketFrame;

class WebSocketFrameTest extends TestCase
{
    #[Test]
    public function withFdReturnsNewInstanceAndKeepsOriginalImmutable(): void
    {
        $frame = new WebSocketFrame(1, 'hello');
        $new = $frame->withFd(2);

        self::assertNotSame($frame, $new);
        self::assertSame(1, $frame->getFd());
        self::assertSame(2, $new->getFd());
        self::assertSame('hello', $new->getData());
    }

    #[Test]
    public function withDataReturnsNewInstanceAndKeepsOriginalImmutable(): void
    {
        $frame = new WebSocketFrame(1);
        $new = $frame->withData('world');

        self::assertNotSame($frame, $new);
        self::assertSame('', $frame->getData());
        self::assertSame('world', $new->getData());
        self::assertSame(1, $new->getFd());
    }
}
