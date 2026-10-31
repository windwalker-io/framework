<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Event;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Http\Event\RequestEvent;
use Windwalker\Http\Output\OutputInterface;
use Windwalker\Http\Request\ServerRequest;

class RequestEventTest extends TestCase
{
    #[Test]
    public function attributeAccessorsStoreAndReadNamedValues(): void
    {
        $event = new RequestEvent(new ServerRequest(), $this->createStub(OutputInterface::class));

        self::assertNull($event->getAttribute('foo'));
        self::assertSame($event, $event->setAttribute('foo', 'bar'));
        self::assertSame('bar', $event->getAttribute('foo'));
        self::assertSame(['foo' => 'bar'], $event->attributes);
    }
}
