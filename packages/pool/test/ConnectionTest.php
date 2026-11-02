<?php

declare(strict_types=1);

namespace Windwalker\Pool\Test;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
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
    public function leakWarningGoesToConnectionLoggerInsteadOfTriggeringError(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('warning')
            ->with($this->stringContains('was not released but destruct'));

        $conn = new StubConnection();
        $conn->logger = $logger;
        $conn->setPool(static::createPool());
        $conn->setState(ConnectionState::ACTIVE);

        self::assertSame([], $this->captureUserWarnings(static function () use (&$conn) {
            $conn = null;
        }));
    }

    #[Test]
    public function leakWarningGoesToPoolLoggerWhenConnectionHasNone(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('warning')
            ->with($this->stringContains('was not released but destruct'));

        $pool = static::createPool();
        $pool->setLogger($logger);

        $conn = new StubConnection();
        $conn->setPool($pool);
        $conn->setState(ConnectionState::ACTIVE);

        self::assertSame([], $this->captureUserWarnings(static function () use (&$conn) {
            $conn = null;
        }));
    }

    #[Test]
    public function leakWarningFallsBackToTriggerErrorWhenOnlyNullLoggerExists(): void
    {
        $pool = static::createPool();
        $conn = new StubConnection();
        $conn->setPool($pool);
        $conn->setState(ConnectionState::ACTIVE);

        $messages = $this->captureUserWarnings(static function () use (&$conn) {
            $conn = null;
        });

        self::assertCount(1, $messages);
    }

    /**
     * @return  string[]
     */
    protected function captureUserWarnings(\Closure $callback): array
    {
        $messages = [];

        set_error_handler(
            static function (int $no, string $message) use (&$messages) {
                $messages[] = $message;

                return true;
            },
            E_USER_WARNING
        );

        try {
            $callback();
        } finally {
            restore_error_handler();
        }

        return $messages;
    }

    #[Test]
    public function leakReportIncludesStructuredContext(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('warning')
            ->with(
                $this->stringContains('was not released but destruct'),
                $this->callback(
                    fn(array $context) => is_int($context['connection_id'])
                        && is_subclass_of($context['connection'], StubConnection::class)
                        && $context['pool'] === StubConnectionPool::class
                        && $context['last_time'] === 1700000000
                        && $context['last_used_at'] === date('Y-m-d H:i:s', 1700000000)
                        && $context['uses'] === 1
                )
            );

        $pool = static::createPool();
        $conn = new class () extends StubConnection {
            public function getLastTime(): int
            {
                return 1700000000;
            }
        };
        $conn->logger = $logger;
        $conn->setPool($pool);
        $conn->incrementUses();
        $conn->setState(ConnectionState::ACTIVE);

        $conn = null;
    }

    #[Test]
    public function leakIsLoggedEvenWhenLeakWarningDisabled(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning');

        $pool = static::createPool();
        $conn = new StubConnection();
        $conn->leakWarning = false;
        $conn->logger = $logger;
        $conn->setPool($pool);
        $conn->setState(ConnectionState::ACTIVE);

        self::assertSame([], $this->captureUserWarnings(static function () use (&$conn) {
            $conn = null;
        }));
    }

    #[Test]
    public function destructDoesNotTriggerWarningWhenLeakWarningDisabledAndNoLogger(): void
    {
        $pool = static::createPool();
        $conn = new StubConnection();
        $conn->leakWarning = false;
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

    #[Test]
    public function destructDoesNotWarnWhenProcessExitsWithActiveConnection(): void
    {
        $autoload = dirname(__DIR__, 3) . '/vendor/autoload.php';
        $script = sprintf(
            'require %s; $pool = new %s([], new %s()); $c = new %s(); $c->setPool($pool); '
            . '$c->setState(%s::ACTIVE); exit(0);',
            var_export($autoload, true),
            '\\' . StubConnectionPool::class,
            '\\' . PhpStack::class,
            '\\' . StubConnection::class,
            '\\' . ConnectionState::class
        );

        exec(
            sprintf('%s -d display_errors=1 -d error_reporting=-1 -r %s 2>&1', PHP_BINARY, escapeshellarg($script)),
            $output,
            $code
        );

        self::assertSame(0, $code);
        self::assertStringNotContainsString('was not released but destruct', implode("\n", $output));
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
