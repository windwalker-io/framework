<?php

declare(strict_types=1);

namespace Windwalker\Queue\Test;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Queue\Driver\DatabaseQueueDriver;
use Windwalker\Queue\Queue;
use Windwalker\Queue\Test\Stub\TestJob;
use Windwalker\Test\Traits\DatabaseTestTrait;

/**
 * The QueueAdapterTest class.
 */
class QueueDatabaseTest extends TestCase
{
    use DatabaseTestTrait;

    protected ?Queue $instance;

    /**
     * @see  Queue::push
     */
    #[Test]
    public function pushStoresJobInDefaultChannelAndReturnsId(): void
    {
        $job = new TestJob(['Hello']);

        $result = $this->instance->push($job, 0);

        self::assertEquals(
            '1',
            $result
        );

        $job = self::$db->select('*')
            ->from('queue_jobs')
            ->where('id', 1)
            ->get();

        self::assertEquals(
            'default',
            $job->channel
        );

        $body = json_decode($job->body, true);

        self::assertEquals(
            ['Hello'],
            unserialize($body['job'])->logs
        );
    }

    /**
     * @see  Queue::pop
     */
    #[Test]
    public function popReturnsMessageWithIncrementedAttemptsAndRunnableJob(): void
    {
        $message = $this->instance->pop();

        self::assertEquals(
            1,
            $message->getAttempts()
        );

        /** @var TestJob $job */
        $job = $message->getJob();
        $job->__invoke();

        self::assertEquals(
            ['Hello'],
            $job->executed
        );
    }

    /**
     * @see  Queue::release
     */
    #[Test]
    public function releaseClearsReservedTimeOfPoppedMessage(): void
    {
        $job = new TestJob(['Welcome']);

        $result = $this->instance->push($job, 0);

        $message = $this->instance->pop();

        $r = self::$db->select('*')
            ->from('queue_jobs')
            ->where('id', 2)
            ->get();

        self::assertNotNull($r);

        $this->instance->release($message);

        $item = self::$db->select('*')
            ->from('queue_jobs')
            ->where('id', 2)
            ->get();

        self::assertNull($item->reserved);
    }

    /**
     * @see  Queue::delete
     */
    #[Test]
    public function deleteRemovesJobById(): void
    {
        $this->instance->delete(2);

        $items = self::$db->select('*')
            ->from('queue_jobs')
            ->all();

        self::assertCount(1, $items);
    }

    /**
     * @see  ChannelAwareDriverInterface::getChannels
     */
    #[Test]
    public function getChannelsListsAllUsedChannels(): void
    {
        $this->instance->push(new TestJob(['Foo']), 0, 'foo-channel');
        $this->instance->push(new TestJob(['Bar']), 0, 'bar-channel');

        $channels = [...$this->instance->getDriver()->getChannels()];

        sort($channels);

        self::assertEquals(
            ['bar-channel', 'default', 'foo-channel'],
            $channels
        );

        self::$db->delete('queue_jobs')
            ->whereIn('channel', ['foo-channel', 'bar-channel'])
            ->execute();
    }

    /**
     * @see  Queue::getMessageByJob
     */
    #[Test]
    public function getMessageByJobReturnsMessageOfJob(): void
    {
        $message = $this->instance->getMessageByJob(new TestJob(['a']), ['foo' => 'bar']);

        self::assertSame(TestJob::class, $message->getName());
        self::assertSame(TestJob::class, $message->getData()['class']);
        self::assertSame('bar', $message->getData()['foo']);
        self::assertNotNull($message->serializedJob);
        self::assertInstanceOf(TestJob::class, $message->getJob());
    }

    /**
     * @see  Queue::pushRaw
     */
    #[Test]
    public function pushRawStoresRawBody(): void
    {
        $id = $this->instance->pushRaw('{"job":"raw-body"}', 0, 'raw-channel');

        $row = self::$db->select('*')
            ->from('queue_jobs')
            ->where('id', $id)
            ->get();

        self::assertSame('raw-channel', $row->channel);
        self::assertSame('raw-body', json_decode($row->body, true)['job']);

        self::$db->delete('queue_jobs')->where('id', $id)->execute();
    }

    /**
     * @see  Queue::defer
     */
    #[Test]
    public function deferReleasesMessageWithoutCountingAttempt(): void
    {
        $this->instance->push(new TestJob(['later']), 0, 'defer-channel');
        $message = $this->instance->pop('defer-channel');

        self::assertSame(1, $message->getAttempts());

        $this->instance->defer($message, 0);

        // Some drivers re-create the row on defer, so look it up by channel.
        $rows = self::$db->select('*')->from('queue_jobs')->where('channel', 'defer-channel')->all()->dump();

        self::assertCount(1, $rows);
        self::assertEquals(0, $rows[0]['attempts']);
        self::assertNull($rows[0]['reserved']);

        self::$db->delete('queue_jobs')->where('channel', 'defer-channel')->execute();
    }

    #[Before]
    protected function createQueueInstance(): void
    {
        $this->instance = new Queue(
            new DatabaseQueueDriver(static::$db)
        );
    }

    /**
     * setupDatabase
     *
     * @return  void
     */
    protected static function setupDatabase(): void
    {
        self::createDatabase('pdo_mysql');
        self::importFromFile(__DIR__ . '/../resources/sql/queue_jobs.sql');
    }
}
