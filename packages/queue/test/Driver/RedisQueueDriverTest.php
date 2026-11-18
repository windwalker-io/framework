<?php

declare(strict_types=1);

namespace Windwalker\Queue\Test\Driver;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Throwable;
use Windwalker\Queue\Driver\RedisQueueDriver;
use Windwalker\Queue\QueueMessage;
use Windwalker\Queue\Test\Stub\TestJob;
use Windwalker\Utilities\Env;

/**
 * Runs against a local Redis, see `redis` in docker-compose.dist.yml.
 * Skipped unless REDIS_ENABLED is set. REDIS_PORT changes the port.
 */
class RedisQueueDriverTest extends TestCase
{
    private RedisQueueDriver $driver;

    private string $channel;

    protected function setUp(): void
    {
        if (!Env::get('REDIS_ENABLED')) {
            self::markTestSkipped('Redis tests are not enabled. Set REDIS_ENABLED=1 to run them.');
        }

        $this->channel = 'wwtest' . bin2hex(random_bytes(4));

        $this->driver = new RedisQueueDriver(
            $this->channel,
            [
                'host' => '127.0.0.1',
                'port' => (int) ($_SERVER['REDIS_PORT'] ?? $_ENV['REDIS_PORT'] ?? 6379),
                'prefix' => 'wwtest:',
            ]
        );

        try {
            $this->driver->getRedis();
        } catch (Throwable $e) {
            unset($this->driver);
            self::markTestSkipped('Redis server is not available: ' . $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        if (isset($this->driver)) {
            $redis = $this->driver->getRedis();

            foreach ($redis->keys('wwtest:wwtest*') as $key) {
                $redis->del($key);
            }

            $this->driver->close();
        }
    }

    #[Test]
    public function pushPopAndDeleteRoundTrip(): void
    {
        $id = $this->driver->push(new QueueMessage(new TestJob(['redis'])));

        self::assertSame(1, $this->driver->size());

        $message = $this->driver->pop();

        self::assertSame($id, $message->getId());
        self::assertSame(1, $message->getAttempts());
        self::assertSame($this->channel, $message->getChannel());
        self::assertSame(1, $this->driver->size());

        $this->driver->delete($message);

        self::assertSame(0, $this->driver->size());
        self::assertNull($this->driver->pop());
    }

    #[Test]
    public function popReturnsNullOnEmptyChannel(): void
    {
        self::assertNull($this->driver->pop());
    }

    #[Test]
    public function popFromOtherChannel(): void
    {
        $other = $this->channel . 'b';
        $this->driver->push(new QueueMessage(new TestJob(['redis']))->setChannel($other));

        self::assertNull($this->driver->pop());
        self::assertNotNull($this->driver->pop($other));
    }

    #[Test]
    public function messagesAreFifo(): void
    {
        $first = $this->driver->push(new QueueMessage(new TestJob(['1'])));
        $second = $this->driver->push(new QueueMessage(new TestJob(['2'])));

        self::assertSame($first, $this->driver->pop()->getId());
        self::assertSame($second, $this->driver->pop()->getId());
    }

    #[Test]
    public function releaseKeepsAttemptCountedByPop(): void
    {
        $id = $this->driver->push(new QueueMessage(new TestJob(['redis'])));

        $message = $this->driver->pop();
        $message->setDelay(0);
        $this->driver->release($message);

        self::assertSame(1, $this->driver->size());

        $message = $this->driver->pop();

        self::assertSame($id, $message->getId());
        self::assertSame(2, $message->getAttempts());
    }

    #[Test]
    public function deferDoesNotAddAttemptAndKeepsPreviousFailures(): void
    {
        $this->driver->push(new QueueMessage(new TestJob(['redis'])));

        $message = $this->driver->pop();
        $message->setDelay(0);
        $this->driver->release($message);

        $message = $this->driver->pop();
        self::assertSame(2, $message->getAttempts());

        $message->setDelay(0);
        $this->driver->defer($message);

        $message = $this->driver->pop();

        self::assertSame(2, $message->getAttempts());
        self::assertSame(1, $message->getDeferTimes());

        $message->setDelay(0);
        $this->driver->defer($message);

        $message = $this->driver->pop();

        self::assertSame(2, $message->getAttempts());
        self::assertSame(2, $message->getDeferTimes());
    }

    #[Test]
    public function createdAtIsKeptAcrossDefer(): void
    {
        $message = new QueueMessage(new TestJob(['redis']));
        $message->setCreatedAt(new \DateTimeImmutable('-120 seconds'));
        $this->driver->push($message);

        $message = $this->driver->pop();
        self::assertGreaterThanOrEqual(120, $message->age);

        $message->setDelay(0);
        $this->driver->defer($message);

        $message = $this->driver->pop();
        self::assertGreaterThanOrEqual(120, $message->age);
        self::assertLessThan(130, $message->age);
    }

    #[Test]
    public function delayedMessageIsHiddenThenDelivered(): void
    {
        $this->driver->push(new QueueMessage(new TestJob(['redis']), [], 1));

        self::assertNull($this->driver->pop());
        self::assertSame(1, $this->driver->size());

        sleep(2);

        $message = $this->driver->pop();

        self::assertNotNull($message);
        self::assertSame(1, $message->getAttempts());
    }

    #[Test]
    public function releaseAndDeferApplyDelay(): void
    {
        $this->driver->push(new QueueMessage(new TestJob(['redis'])));

        $message = $this->driver->pop();
        $message->setDelay(60);
        $this->driver->defer($message);

        self::assertNull($this->driver->pop());
        self::assertSame(1, $this->driver->size());
    }

    #[Test]
    public function expiredReservationIsRedeliveredAndCounted(): void
    {
        $driver = new RedisQueueDriver(
            $this->channel,
            ['redis' => $this->driver->getRedis(), 'prefix' => 'wwtest:', 'retry_after' => 1]
        );

        $driver->push(new QueueMessage(new TestJob(['redis'])));
        $driver->pop();

        self::assertNull($driver->pop());

        // Simulates a crashed worker: the job is never deleted.
        sleep(2);

        $message = $driver->pop();

        self::assertNotNull($message);
        self::assertSame(2, $message->getAttempts());
    }
}
