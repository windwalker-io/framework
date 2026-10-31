<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Enum;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Test\Stub\Enum\StubSingletonEnum;

class EnumSingletonTest extends TestCase
{
    #[Test]
    public function staticCallReturnsSameInstanceEachTime(): void
    {
        self::assertSame(StubSingletonEnum::FOO(), StubSingletonEnum::FOO());
        self::assertNotSame(StubSingletonEnum::FOO(), StubSingletonEnum::BAR());
        self::assertSame('foo', StubSingletonEnum::FOO()->getValue());
    }

    #[Test]
    public function fromResolvesInstanceByValue(): void
    {
        self::assertSame(StubSingletonEnum::BAR(), StubSingletonEnum::from('bar'));
    }
}
