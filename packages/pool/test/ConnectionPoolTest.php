<?php

declare(strict_types=1);

namespace Windwalker\Pool\Test;

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
    public function testConstruct(): void
    {
        self::assertEquals(3, $this->instance->count());
    }

    /**
     * @see  AbstractPool::count
     */
    public function testCount(): void
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
    public function testRelease(): void
    {
        $conn = $this->instance->getConnection();

        self::assertEquals(2, $this->instance->count());

        $this->instance->release($conn);

        self::assertEquals(3, $this->instance->count());
    }

    /**
     * @see  AbstractPool::close
     */
    public function testClose(): void
    {
        $this->instance->close();

        self::assertEquals(0, $this->instance->count());
    }

    /**
     * @see  AbstractPool::totalCount
     */
    public function testTotalCount(): void
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
    public function testCreateConnection(): void
    {
        self::assertInstanceOf(
            StubConnection::class,
            $this->instance->createConnection()
        );
    }

    /**
     * @see  AbstractPool::setLogger
     */
    public function testSetLogger(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    protected function setUp(): void
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

    protected function tearDown(): void
    {
    }
}
