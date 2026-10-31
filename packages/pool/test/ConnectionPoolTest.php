<?php

declare(strict_types=1);

namespace Windwalker\Pool\Test;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Pool\AbstractPool;
use Psr\Log\LoggerInterface;
use Windwalker\Pool\ConnectionInterface;
use Windwalker\Pool\ConnectionPool;
use Windwalker\Pool\Enum\ConnectionState;
use Windwalker\Pool\Enum\Heartbeat;
use Windwalker\Pool\Exception\ConnectionPoolException;
use Windwalker\Pool\PoolOptions;
use Windwalker\Pool\Stack\PhpStack;
use Windwalker\Pool\Stack\SingleStack;
use Windwalker\Pool\Stack\StackInterface;
use Windwalker\Pool\Stack\TimerSupportedInterface;
use Windwalker\Pool\Test\Stub\StubAgingConnection;
use Windwalker\Pool\Test\Stub\StubConnection;

/**
 * The ConnectionPoolTest class.
 */
class ConnectionPoolTest extends TestCase
{
    protected ?AbstractPool $instance;

    /**
     * @see  AbstractPool::__construct
     */
    #[Test]
    public function initFillsPoolToMinSize(): void
    {
        self::assertEquals(3, $this->instance->count());
    }

    /**
     * @see  AbstractPool::count
     */
    #[Test]
    public function countReflectsIdleConnectionsAfterGetAndRelease(): void
    {
        self::assertEquals(3, $this->instance->count());

        $conn = $this->instance->getConnection();

        self::assertEquals(2, $this->instance->count());

        $conn->release();

        self::assertEquals(3, $this->instance->count());
    }

    /**
     * @see  AbstractPool::release
     */
    #[Test]
    public function releaseReturnsConnectionToPool(): void
    {
        $conn = $this->instance->getConnection();

        self::assertEquals(2, $this->instance->count());

        $this->instance->release($conn);

        self::assertEquals(3, $this->instance->count());
    }

    /**
     * @see  AbstractPool::close
     */
    #[Test]
    public function closeEmptiesPool(): void
    {
        $this->instance->close();

        self::assertEquals(0, $this->instance->count());
    }

    /**
     * @see  AbstractPool::totalCount
     */
    #[Test]
    public function totalCountDecreasesWhenConnectionIsDropped(): void
    {
        $conn0 = $this->instance->getConnection();

        self::assertEquals(3, $this->instance->totalCount());

        $conn1 = $this->instance->getConnection();
        $this->instance->dropConnection($conn1);

        self::assertEquals(2, $this->instance->totalCount());

        $conn0->release();
    }

    /**
     * @see  AbstractPool::createConnection
     */
    #[Test]
    public function createConnectionUsesConnectionBuilder(): void
    {
        self::assertInstanceOf(
            StubConnection::class,
            $this->instance->createConnection()
        );
    }

    /**
     * @see  AbstractPool::setLogger
     */
    #[Test]
    public function setLoggerAssignsLogger(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    #[Test]
    public function initIsIdempotent(): void
    {
        $this->instance->init();

        self::assertSame(3, $this->instance->totalCount());
        self::assertSame(3, $this->instance->count());
    }

    #[Test]
    public function initWithSingleStackCreatesOnlyOneConnection(): void
    {
        $pool = new ConnectionPool(['maxSize' => 3, 'minSize' => 1], new SingleStack());
        $pool->setConnectionBuilder(fn() => self::leakSafe(new StubConnection()));
        $pool->init();

        self::assertSame(1, $pool->totalCount());
        self::assertSame(1, $pool->count());
    }

    #[Test]
    public function getConnectionMarksConnectionActiveAndCountsUses(): void
    {
        $conn = $this->instance->getConnection();

        self::assertSame(ConnectionState::ACTIVE, $conn->getState());
        self::assertSame(1, $conn->getCurrentUses());
    }

    #[Test]
    public function getConnectionCreatesNewConnectionWhenBelowMinSize(): void
    {
        $pool = $this->createAgingPool(['maxSize' => 3, 'minSize' => 2]);
        $pool->close();

        self::assertSame(0, $pool->totalCount());

        $conn = $pool->getConnection();

        self::assertSame(ConnectionState::ACTIVE, $conn->getState());
        self::assertSame(1, $pool->totalCount());
    }

    #[Test]
    public function getConnectionCreatesNewConnectionWhenStackEmptyAndBelowMaxSize(): void
    {
        $conns = [
            $this->instance->getConnection(),
            $this->instance->getConnection(),
            $this->instance->getConnection(),
        ];
        $this->instance->dropConnection($conns[0]);

        self::assertSame(2, $this->instance->totalCount());

        $new = $this->instance->getConnection();

        self::assertSame(3, $this->instance->totalCount());
        self::assertNotContains($new, [$conns[0]]);
    }

    #[Test]
    public function getConnectionThrowsWhenMaxSizeReachedAndNothingToPop(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->instance->getConnection();
        }

        $this->expectException(ConnectionPoolException::class);

        $this->instance->getConnection();
    }

    #[Test]
    public function getConnectionThrowsWhenWaitingConsumersReachMaxWait(): void
    {
        $stack = $this->createStub(StackInterface::class);
        $stack->method('count')->willReturn(0);
        $stack->method('waitingCount')->willReturn(5);

        $pool = new ConnectionPool(['maxSize' => 1, 'minSize' => 0, 'maxWait' => 2], $stack);
        $pool->setConnectionBuilder(fn() => self::leakSafe(new StubConnection()));
        $pool->init();

        $this->expectException(ConnectionPoolException::class);
        $this->expectExceptionMessageIsOrContains('Waiting Consumer is full. max_wait=2');

        $pool->getConnection();
    }

    #[Test]
    public function getConnectionDropsConnectionsOverIdleTimeout(): void
    {
        $built = [];
        $pool = $this->createAgingPool(['maxSize' => 2, 'minSize' => 0, 'idleTimeout' => 10], $built);

        foreach ($built as $conn) {
            $conn->lastTimeOverride = time() - 100;
        }

        $old = $built;
        $conn = $pool->getConnection();

        self::assertNotContains($conn, $old);
        self::assertSame(1, $pool->totalCount());
        self::assertSame(ConnectionState::ABANDONED, $built[0]->getState());
    }

    #[Test]
    public function getConnectionDropsConnectionsOverMaxLifetime(): void
    {
        $built = [];
        $pool = $this->createAgingPool(
            ['maxSize' => 2, 'minSize' => 0, 'idleTimeout' => -1, 'maxLifetime' => 10],
            $built
        );

        foreach ($built as $conn) {
            $conn->createdTimeOverride = time() - 100;
        }

        $old = $built;
        $conn = $pool->getConnection();

        self::assertNotContains($conn, $old);
        self::assertSame(1, $pool->totalCount());
    }

    #[Test]
    public function getConnectionDropsAbandonedConnections(): void
    {
        $built = [];
        $pool = $this->createAgingPool(['maxSize' => 2, 'minSize' => 0], $built);

        foreach ($built as $conn) {
            $conn->setState(ConnectionState::ABANDONED);
        }

        $old = $built;
        $conn = $pool->getConnection();

        self::assertNotContains($conn, $old);
        self::assertSame(1, $pool->totalCount());
    }

    #[Test]
    public function getConnectionDropsConnectionsFailingLazyHeartbeat(): void
    {
        $built = [];
        $pool = $this->createAgingPool(
            ['maxSize' => 2, 'minSize' => 0, 'heartbeat' => Heartbeat::LAZY],
            $built
        );

        foreach ($built as $conn) {
            $conn->pingResult = false;
        }

        $old = $built;
        $conn = $pool->getConnection();

        self::assertNotContains($conn, $old);
        self::assertSame(1, $pool->totalCount());
    }

    #[Test]
    public function getConnectionReusesConnectionPassingLazyHeartbeat(): void
    {
        $built = [];
        $pool = $this->createAgingPool(
            ['maxSize' => 2, 'minSize' => 0, 'heartbeat' => Heartbeat::LAZY],
            $built
        );

        $conn = $pool->getConnection();

        self::assertContains($conn, $built);
        self::assertSame(2, $pool->totalCount());
    }

    #[Test]
    public function releaseDropsConnectionReachingMaxUses(): void
    {
        $pool = $this->createAgingPool(['maxSize' => 3, 'minSize' => 0, 'maxUses' => 1]);

        $conn = $pool->getConnection();
        $pool->release($conn);

        self::assertSame(ConnectionState::ABANDONED, $conn->getState());
        self::assertSame(2, $pool->totalCount());
        self::assertSame(2, $pool->count());
    }

    #[Test]
    public function releaseDropsAbandonedConnection(): void
    {
        $conn = $this->instance->getConnection();
        $conn->setState(ConnectionState::ABANDONED);

        $this->instance->release($conn);

        self::assertSame(2, $this->instance->totalCount());
        self::assertSame(2, $this->instance->count());
    }

    #[Test]
    public function releaseDropsConnectionWhenStackIsFull(): void
    {
        $conn = $this->instance->getConnection();
        $this->instance->createConnection();

        self::assertSame(4, $this->instance->totalCount());
        self::assertSame(3, $this->instance->count());

        $this->instance->release($conn);

        self::assertSame(3, $this->instance->totalCount());
        self::assertSame(3, $this->instance->count());
    }

    #[Test]
    public function closeReturnsClosedCountAndDropsAllConnections(): void
    {
        self::assertSame(3, $this->instance->close());
        self::assertSame(0, $this->instance->totalCount());
        self::assertSame(0, $this->instance->close());
    }

    #[Test]
    public function closeLogsWarningWhenDisconnectFails(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('warning')
            ->with($this->stringContains('Disconnect failed'));

        $built = [];
        $pool = $this->createAgingPool(['maxSize' => 1, 'minSize' => 0], $built);
        $pool->setLogger($logger);
        $built[0]->disconnectThrows = true;

        self::assertSame(1, $pool->close());
    }

    #[Test]
    public function timerHandlerLogsLeakedConnectionsAndAbandonsDeadIdleConnections(): void
    {
        $stack = new class () extends PhpStack implements TimerSupportedInterface {
            public ?\Closure $handler = null;

            public bool $stopped = false;

            public function startTimer(\Closure $handler, int $intervalSeconds): void
            {
                $this->handler = $handler;
            }

            public function stopTimer(): void
            {
                $this->stopped = true;
            }
        };

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('warning')
            ->with($this->stringContains('Connection leak detected'));

        $built = [];
        $pool = new ConnectionPool(
            new PoolOptions(
                maxSize: 2,
                minSize: 0,
                leakTimeout: 5,
                heartbeat: Heartbeat::INTERVALS,
                timerInterval: 1
            ),
            $stack,
            $logger
        );
        $pool->setConnectionBuilder(
            function () use (&$built) {
                return $built[] = self::leakSafe(new StubAgingConnection());
            }
        );
        $pool->init();

        self::assertNotNull($stack->handler);

        $leaked = $pool->getConnection();
        $leaked->lastTimeOverride = time() - 100;
        $idle = $built[0] === $leaked ? $built[1] : $built[0];
        $idle->pingResult = false;

        ($stack->handler)();

        self::assertSame(ConnectionState::ABANDONED, $idle->getState());
        self::assertSame(ConnectionState::ACTIVE, $leaked->getState());

        $stack->handler = null;
        unset($pool);

        self::assertTrue($stack->stopped);
    }

    protected static function leakSafe(StubConnection $connection): StubConnection
    {
        $connection->leakProtect = false;

        return $connection;
    }

    /**
     * @param  array<StubAgingConnection>  $built
     */
    protected function createAgingPool(array $options, array &$built = []): ConnectionPool
    {
        $pool = new ConnectionPool($options, new PhpStack());
        $pool->setConnectionBuilder(
            function () use (&$built): ConnectionInterface {
                return $built[] = self::leakSafe(new StubAgingConnection());
            }
        );
        $pool->init();

        return $pool;
    }

    #[Before]
    protected function createPool(): void
    {
        $this->instance = new ConnectionPool(
            [
                ConnectionPool::MAX_SIZE => 3,
                ConnectionPool::MIN_SIZE => 3,
            ],
            new PhpStack()
        );
        $this->instance->setConnectionBuilder(fn() => self::leakSafe(new StubConnection()));
        $this->instance->init();
    }
}
