<?php

declare(strict_types=1);

namespace Windwalker\Queue\Test\Driver;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Windwalker\Queue\Driver\DatabaseQueueDriver;
use Windwalker\Database\DatabaseAdapter;
use Windwalker\Database\Driver\StatementInterface;
use Windwalker\Queue\Driver\PdoQueueDriver;
use Windwalker\Queue\Exception\UnrecoverableException;
use Windwalker\Queue\Enum\DatabaseIdType;
use Windwalker\Queue\Failer\DatabaseQueueFailer;
use Windwalker\Queue\Queue;
use Windwalker\Queue\QueueMessage;
use Windwalker\Queue\Test\Stub\TestJob;
use Windwalker\Test\Traits\DatabaseTestTrait;

/**
 * Covers the UUID id mode of the database drivers and failer, and driver connection handling.
 */
class UuidDatabaseDriverTest extends TestCase
{
    use DatabaseTestTrait;

    #[Test]
    public function databaseDriverPushesPopsAndDeletesWithUuid(): void
    {
        $driver = new DatabaseQueueDriver(self::$db, 'uuid-db', 'queue_jobs_uuid', 60, DatabaseIdType::UUID);

        $id = $driver->push(new QueueMessage(new TestJob(['uuid'])));

        self::assertTrue(Uuid::isValid($id));

        $message = $driver->pop('uuid-db');

        self::assertSame($id, $message->getId());

        $driver->delete($message);

        self::assertNull($driver->pop('uuid-db'));
    }

    #[Test]
    public function pdoDriverPushesWithUuidAndReturnsGeneratedId(): void
    {
        $conn = self::$db->getDriver()->getConnection();
        $driver = new PdoQueueDriver($conn->get(), 'uuid-pdo', 'queue_jobs_uuid', 60, DatabaseIdType::UUID);

        $id = $driver->push(new QueueMessage(new TestJob(['uuid'])));

        $conn->release();

        self::assertTrue(Uuid::isValid($id));

        $row = self::$db->select('*')->from('queue_jobs_uuid')->where('id', $id)->get();

        self::assertSame('uuid-pdo', $row->channel);
    }

    #[Test]
    public function failerAddsAndGetsFailedJobWithUuid(): void
    {
        $failer = new DatabaseQueueFailer(self::$db, 'queue_failed_jobs_uuid', DatabaseIdType::UUID);

        $id = $failer->add('conn', 'chan', '{}', 'Err');

        self::assertTrue(Uuid::isValid($id));
        self::assertSame($id, $failer->get($id)['id']);
        self::assertSame($id, $failer->get(['id' => $id])['id']);
        self::assertTrue($failer->remove($id));
        self::assertNull($failer->get($id));
    }

    #[Test]
    public function failerMakesConditionsWritableByIdType(): void
    {
        $uuid = Uuid::uuid7();
        $failer = new DatabaseQueueFailer(self::$db, 'queue_failed_jobs_uuid', DatabaseIdType::UUID_BIN);

        self::assertSame($uuid->getBytes(), $failer->makeConditionsWritable($uuid->toString()));
        self::assertSame(
            ['id' => $uuid->getBytes(), 'channel' => 'a'],
            $failer->makeConditionsWritable(['id' => $uuid->toString(), 'channel' => 'a'])
        );
    }

    #[Test]
    public function databaseDriverReconnectKeepsConnectionUsable(): void
    {
        $driver = new DatabaseQueueDriver(self::$db, 'reconnect', 'queue_jobs_uuid', 60, DatabaseIdType::UUID);

        self::assertSame($driver, $driver->reconnect());

        $queue = new Queue($driver);

        self::assertTrue(Uuid::isValid($queue->push(new TestJob(), 0, 'reconnect')));
        self::assertContains('reconnect', iterator_to_array($queue->getChannels()));
    }

    #[Test]
    public function databaseDriverPopRethrowsQueryErrorsAndDisablesSkipLocked(): void
    {
        $driver = new DatabaseQueueDriver(self::$db, 'x', 'queue_jobs_not_exists');

        try {
            $driver->pop();
            self::fail('Querying a missing table should throw.');
        } catch (\Exception $e) {
            self::assertNotInstanceOf(UnrecoverableException::class, $e);
        }

        $canSkipLocked = new \ReflectionMethod($driver, 'checkCanSkipLocked');

        self::assertFalse($canSkipLocked->invoke($driver));
    }

    #[Test]
    public function databaseDriverPopConvertsLostConnectionToUnrecoverableException(): void
    {
        $statement = $this->createStub(StatementInterface::class);
        $statement->method('get')->willThrowException(new \RuntimeException('MySQL server has gone away'));

        $db = $this->createStub(DatabaseAdapter::class);
        $db->method('getQuery')->willReturnCallback(fn() => self::$db->getQuery(true));
        $db->method('createQuery')->willReturnCallback(fn() => self::$db->createQuery());
        $db->method('getDriver')->willReturn(self::$db->getDriver());
        $db->method('prepare')->willReturn($statement);
        $db->method('transaction')->willReturnCallback(fn(callable $callback) => $callback());

        $driver = new DatabaseQueueDriver($db, 'x', 'queue_jobs_uuid', 60, DatabaseIdType::UUID);

        $this->expectException(UnrecoverableException::class);

        $driver->pop();
    }

    #[Test]
    public function pdoDriverPopRollsBackAndRethrowsWhenQueryFails(): void
    {
        $statement = $this->createStub(\PDOStatement::class);
        $statement->method('execute')->willThrowException(new \PDOException('select failed'));

        $pdo = $this->createMock(\PDO::class);
        $pdo->expects($this->once())->method('beginTransaction')->willReturn(true);
        $pdo->method('prepare')->willReturn($statement);
        $pdo->expects($this->once())->method('rollBack')->willReturn(true);

        $driver = new PdoQueueDriver($pdo, 'x', 'queue_jobs_uuid');

        $this->expectException(\PDOException::class);
        $this->expectExceptionMessage('select failed');

        $driver->pop();
    }

    protected static function setupDatabase(): void
    {
        self::createDatabase('pdo_mysql');

        self::$db->execute('DROP TABLE IF EXISTS `queue_jobs_uuid`');
        self::$db->execute(
            'CREATE TABLE `queue_jobs_uuid` (
                `id` char(36) NOT NULL PRIMARY KEY,
                `channel` varchar(255) NOT NULL DEFAULT \'\',
                `body` longtext NOT NULL,
                `attempts` tinyint NOT NULL DEFAULT 0,
                `created` datetime DEFAULT NULL,
                `visibility` datetime DEFAULT NULL,
                `reserved` datetime DEFAULT NULL
            )'
        );
        self::$db->execute('DROP TABLE IF EXISTS `queue_failed_jobs_uuid`');
        self::$db->execute(
            'CREATE TABLE `queue_failed_jobs_uuid` (
                `id` char(36) NOT NULL PRIMARY KEY,
                `connection` varchar(255) NOT NULL DEFAULT \'\',
                `channel` varchar(255) NOT NULL DEFAULT \'\',
                `body` longtext NOT NULL,
                `exception` longtext NOT NULL,
                `created` datetime NOT NULL DEFAULT \'1000-01-01 00:00:00\'
            )'
        );
    }
}
