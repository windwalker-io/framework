<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Driver;

use PDOException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Database\Driver\AbstractDriver;
use Windwalker\Database\Driver\ConnectionInterface;
use Windwalker\Database\Driver\Pdo\PdoDriver;

class AbstractDriverTest extends TestCase
{
    public static function connectionMessages(): iterable
    {
        foreach ([
            'MySQL server has gone away',
            'Lost connection to MySQL server during query',
            'SQLSTATE[HY000] [2002] Connection timed out',
            'Connection reset by peer',
            'server closed the connection unexpectedly',
            'SSL connection has been closed unexpectedly',
            'no connection to the server',
            'Communication link failure',
            'Error while sending query packet',
        ] as $message) {
            yield $message => [$message, true];
        }

        yield 'syntax' => ['SQLSTATE[42000]: Syntax error', false];
        yield 'authentication' => ['SQLSTATE[28000]: Access denied', false];
        yield 'deadlock' => ['Deadlock found', false];
    }

    #[Test]
    #[DataProvider('connectionMessages')]
    public function detectsConnectionLoss(string $message, bool $expected): void
    {
        self::assertSame($expected, AbstractDriver::shouldAutoReconnect($message));
        self::assertSame($expected, AbstractDriver::shouldAutoReconnect(new PDOException($message)));
    }

    #[Test]
    public function connectionLossRetriesOnlyOnceAndThenPropagates(): void
    {
        $connection = $this->createMock(ConnectionInterface::class);
        $connection->expects($this->once())->method('reconnect');
        $connection->expects($this->once())->method('release');
        /**
         * Supplies a fake connection to the real retry implementation; needed only by this regression.
         */
        $driver = new class ($connection) extends PdoDriver {
            public function __construct(private ConnectionInterface $testConnection)
            {
                parent::__construct(['driver' => 'pdo_sqlite']);
            }

            public function getConnection(bool $keep = false): ConnectionInterface
            {
                return $this->testConnection;
            }
        };
        $attempts = 0;
        $failure = new PDOException('Connection reset by peer');

        try {
            $driver->useConnection(static function () use (&$attempts, $failure) {
                $attempts++;

                throw $failure;
            });
            self::fail('The second failure must propagate.');
        } catch (PDOException $e) {
            self::assertSame($failure, $e);
        }

        self::assertSame(2, $attempts);
    }
}
