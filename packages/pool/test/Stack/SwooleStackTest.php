<?php

declare(strict_types=1);

namespace Windwalker\Pool\Test\Stack;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\BeforeClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Swoole\Timer;
use Windwalker\Pool\Stack\SwooleStack;
use Windwalker\Pool\Test\Stub\StubConnection;
use Windwalker\Test\Traits\Reactor\SwooleTestTrait;

use function Swoole\Coroutine\run;

/**
 * The SwooleStackTest class.
 */
class SwooleStackTest extends TestCase
{
    use SwooleTestTrait;

    protected ?SwooleStack $instance;

    #[Test]
    public function popWaitsUntilConnectionIsPushedInCoroutine(): void
    {
        $v = null;

        run(
            function () use (&$v) {
                $this->instance->push($stub = new StubConnection());

                $conn = $this->instance->pop();

                Timer::after(
                    300,
                    function () use ($conn) {
                        $this->instance->push($conn);
                    }
                );

                $conn = $this->instance->pop();
                $v = $conn;

                self::assertSame($conn, $stub);
            }
        );

        self::assertNotNull($v);
    }

    #[BeforeClass]
    public static function skipWhenSwooleUnavailable(): void
    {
        self::skipIfSwooleNotInstalled();
    }

    #[Before]
    protected function createStack(): void
    {
        $this->instance = new SwooleStack(10);
    }
}
