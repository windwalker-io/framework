<?php

declare(strict_types=1);

namespace Windwalker\Pool\Test;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Pool\AbstractConnection;
use Windwalker\Pool\AbstractPool;
use Windwalker\Pool\Enum\ConnectionState;
use Windwalker\Pool\Exception\ConnectionPoolException;
use Windwalker\Pool\Stack\PhpStack;
use Windwalker\Pool\Test\Stub\StubConnection;
use Windwalker\Pool\Test\Stub\StubConnectionPool;

/**
 * The ConnectionTest class.
 */
class ConnectionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected ?AbstractConnection $instance;

    /**
     * @see  AbstractConnection::setState
     */
    #[Test]
    public function setStateSwitchesBetweenActiveAndInactive(): void
    {
        $this->instance->setState(ConnectionState::ACTIVE);

        self::assertEquals(ConnectionState::ACTIVE, $this->instance->getState());

        $this->instance->setState(ConnectionState::INACTIVE);

        self::assertEquals(ConnectionState::INACTIVE, $this->instance->getState());
    }

    /**
     * @see  AbstractConnection::getLastTime
     */
    #[Test]
    public function updateLastTimeChangesLastTimeFromZero(): void
    {
        self::assertEquals(0, $this->instance->getLastTime());

        $this->instance->updateLastTime();

        self::assertNotEquals(0, $this->instance->getLastTime());
    }

    /**
     * @see  AbstractConnection::getId
     */
    #[Test]
    public function idIsAssignedFromPoolSerialWhenPoolIsSet(): void
    {
        self::assertEquals(0, $this->instance->getId());

        $this->instance->setPool(static::createPool());

        self::assertEquals(1, $this->instance->getId());
    }

    /**
     * @see  AbstractConnection::release
     */
    #[Test]
    public function releaseDelegatesToPool(): void
    {
        $pool = Mockery::mock(AbstractPool::class);
        $pool->shouldReceive('release')
            ->once();

        $pool->shouldReceive('getSerial')
            ->once()
            ->andReturn(123);

        $this->instance->setPool($pool);

        $this->instance->release(true);
    }

    /**
     * @see  AbstractConnection::reconnect
     */
    #[Test]
    public function reconnectReturnsConnectionResult(): void
    {
        $this->instance->reconnect();

        self::assertEquals('Hello', $this->instance->reconnect());
    }

    #[Test]
    public function releaseThrowsWhenNoPoolAssigned(): void
    {
        $this->expectException(ConnectionPoolException::class);
        $this->expectExceptionMessageIs('No assigned pool of this connection.');

        $this->instance->release();
    }

    #[Test]
    public function releaseSkipsPoolWhenConnectionInactiveAndNotForced(): void
    {
        $pool = Mockery::mock(AbstractPool::class);
        $pool->shouldReceive('getSerial')->andReturn(1);
        $pool->shouldNotReceive('release');

        $this->instance->setPool($pool);
        $this->instance->release();
    }

    #[Test]
    public function incrementUsesReturnsNewUseCount(): void
    {
        self::assertSame(1, $this->instance->incrementUses());
        self::assertSame(2, $this->instance->incrementUses());
    }

    #[Test]
    public function destructWarnsWhenActiveConnectionWasNotReleased(): void
    {
        $pool = static::createPool();
        $conn = new StubConnection();
        $conn->setPool($pool);
        $conn->setState(ConnectionState::ACTIVE);

        $messages = [];
        set_error_handler(
            function (int $no, string $message) use (&$messages) {
                $messages[] = $message;

                return true;
            },
            E_USER_WARNING
        );

        try {
            unset($conn);
        } finally {
            restore_error_handler();
        }

        self::assertCount(1, $messages);
        self::assertStringContainsString('was not released but destruct', $messages[0]);
    }

    #[Test]
    public function destructDoesNotWarnWhenLeakProtectDisabled(): void
    {
        $pool = static::createPool();
        $conn = new StubConnection();
        $conn->leakProtect = false;
        $conn->setPool($pool);
        $conn->setState(ConnectionState::ACTIVE);

        $messages = [];
        set_error_handler(
            function (int $no, string $message) use (&$messages) {
                $messages[] = $message;

                return true;
            },
            E_USER_WARNING
        );

        try {
            unset($conn);
        } finally {
            restore_error_handler();
        }

        self::assertSame([], $messages);
    }

    #[Before]
    protected function createConnection(): void
    {
        $this->instance = new StubConnection();
    }

    protected static function createPool(): StubConnectionPool
    {
        return new StubConnectionPool(
            [],
            new PhpStack()
        );
    }
}
