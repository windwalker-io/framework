<?php

declare(strict_types=1);

namespace Windwalker\Pool\Test\Stack;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Pool\Exception\ConnectionPoolException;
use Windwalker\Pool\Stack\SingleStack;
use Windwalker\Pool\Test\Stub\StubConnection;

class SingleStackTest extends TestCase
{
    protected SingleStack $instance;

    #[Before]
    protected function createStack(): void
    {
        $this->instance = new SingleStack();
    }

    #[Test]
    public function popReturnsPushedConnectionAndEmptiesStack(): void
    {
        $this->instance->push($conn = new StubConnection());

        self::assertSame(1, $this->instance->count());
        self::assertSame($conn, $this->instance->pop());
        self::assertSame(0, $this->instance->count());
    }

    #[Test]
    public function pushReplacesExistingConnection(): void
    {
        $this->instance->push(new StubConnection());
        $this->instance->push($second = new StubConnection());

        self::assertSame(1, $this->instance->count());
        self::assertSame($second, $this->instance->pop());
    }

    #[Test]
    public function popThrowsWhenStackIsEmpty(): void
    {
        $this->expectException(ConnectionPoolException::class);
        $this->expectExceptionMessageIsOrContains('No connection exists');

        $this->instance->pop();
    }

    #[Test]
    public function waitingCountIsAlwaysZero(): void
    {
        self::assertSame(0, $this->instance->waitingCount());
    }
}
