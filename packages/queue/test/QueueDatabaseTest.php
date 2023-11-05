<?php

declare(strict_types=1);

namespace Windwalker\Queue\Test;

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
    public function testPush(): void
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
    public function testPop(): void
    {
        $message = $this->instance->pop();

        self::assertEquals(
            1,
            $message->getAttempts()
        );

        /** @var TestJob $job */
        $job = unserialize($message->getSerializedJob());
        $job->__invoke();

        self::assertEquals(
            ['Hello'],
            $job->executed
        );
    }

    /**
     * @see  Queue::setDriver
     */
    public function testSetDriver(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Queue::release
     */
    public function testRelease(): void
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
    public function testDelete(): void
    {
        $this->instance->delete(2);

        $items = self::$db->select('*')
            ->from('queue_jobs')
            ->all();

        self::assertCount(1, $items);
    }

    /**
     * @see  Queue::getDriver
     */
    public function testGetDriver(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Queue::__construct
     */
    public function testConstruct(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Queue::getMessageByJob
     */
    public function testGetMessageByJob(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  Queue::pushRaw
     */
    public function testPushRaw(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    protected function setUp(): void
    {
        $this->instance = new Queue(
            new DatabaseQueueDriver(static::$db)
        );
    }

    protected function tearDown(): void
    {
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
