<?php

declare(strict_types=1);

namespace Windwalker\Queue\Test\Driver;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Windwalker\Queue\Driver\DatabaseQueueDriver;
use Windwalker\Queue\Driver\PdoQueueDriver;
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
