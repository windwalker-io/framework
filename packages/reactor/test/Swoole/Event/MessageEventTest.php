<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Test\Swoole\Event;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Reactor\Swoole\Event\MessageEvent;

class MessageEventTest extends TestCase
{
    #[Test]
    public function dataPropertyReturnsString(): void
    {
        $property = new \ReflectionProperty(MessageEvent::class, 'data');

        self::assertSame('string', $property->getType()->getName());
    }
}
