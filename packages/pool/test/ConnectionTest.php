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
