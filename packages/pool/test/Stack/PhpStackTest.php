<?php

declare(strict_types=1);

namespace Windwalker\Pool\Test\Stack;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Pool\Exception\ConnectionPoolException;
use Windwalker\Pool\Stack\PhpStack;
use Windwalker\Pool\Test\Stub\StubConnection;

class PhpStackTest extends TestCase
{
    protected PhpStack $instance;

    #[Before]
    protected function createStack(): void
    {
        $this->instance = new PhpStack();
    }

    #[Test]
    public function popReturnsConnectionsInLastInFirstOutOrder(): void
    {
        $this->instance->push($first = new StubConnection());
        $this->instance->push($second = new StubConnection());

        self::assertSame(2, $this->instance->count());
        self::assertSame($second, $this->instance->pop());
        self::assertSame($first, $this->instance->pop());
        self::assertSame(0, $this->instance->count());
    }

    #[Test]
    public function popThrowsWhenStackIsEmpty(): void
    {
        $this->expectException(ConnectionPoolException::class);
        $this->expectExceptionMessageIs('No connections in pool');

        $this->instance->pop();
    }

    #[Test]
    public function waitingCountIsAlwaysZero(): void
    {
        self::assertSame(0, $this->instance->waitingCount());
    }
}
