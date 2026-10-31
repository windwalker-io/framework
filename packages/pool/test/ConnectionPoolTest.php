<?php

declare(strict_types=1);

namespace Windwalker\Pool\Test;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Pool\AbstractPool;
use Windwalker\Pool\ConnectionPool;
use Windwalker\Pool\Stack\PhpStack;
use Windwalker\Pool\Test\Stub\StubConnection;
use Windwalker\Utilities\Serial;

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
        $this->instance->setConnectionBuilder(fn() => new StubConnection());
        $this->instance->init();
    }
}
