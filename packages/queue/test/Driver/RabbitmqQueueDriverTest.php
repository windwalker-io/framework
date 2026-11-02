<?php

declare(strict_types=1);

namespace Windwalker\Queue\Test\Driver;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Throwable;
use Windwalker\Queue\Driver\RabbitmqQueueDriver;
use Windwalker\Queue\QueueMessage;
use Windwalker\Queue\Test\Stub\TestJob;
use Windwalker\Utilities\Env;

/**
 * Runs against a local RabbitMQ, see `rabbitmq` in docker-compose.dist.yml.
 * Set WINDWALKER_TEST_RABBITMQ_HOST / _PORT to change the endpoint. Skipped unless RABBITMQ_ENABLED is set.
 */
class RabbitmqQueueDriverTest extends TestCase
{
    private RabbitmqQueueDriver $driver;

    private string $channel;

    protected function setUp(): void
    {
        if (!Env::get('RABBITMQ_ENABLED')) {
            self::markTestSkipped('RabbitMQ tests are not enabled. Set RABBITMQ_ENABLED=1 to run them.');
        }

        $this->channel = 'wwtest' . bin2hex(random_bytes(4));

        $this->driver = new RabbitmqQueueDriver(
            $this->channel,
            [
                'host' => getenv('WINDWALKER_TEST_RABBITMQ_HOST') ?: 'localhost',
                'port' => (int) (getenv('WINDWALKER_TEST_RABBITMQ_PORT') ?: 5672),
            ]
        );

        try {
            $this->driver->getChannel();
        } catch (Throwable $e) {
            unset($this->driver);
            self::markTestSkipped('RabbitMQ server is not available: ' . $e->getMessage());
        }
    }

    protected function tearDown(): void
    {
        if (isset($this->driver)) {
            try {
                $channel = $this->driver->getChannel();
                $channel->queue_delete($this->channel);
                $channel->queue_delete($this->channel . '.delay.1s');
                $channel->queue_delete($this->channel . '.delay.60s');
            } catch (Throwable) {
            }

            $this->driver->close();
        }
    }

    #[Test]
    public function pushPopAndDeleteRoundTrip(): void
    {
        $id = $this->driver->push(new QueueMessage(new TestJob(['rabbit'])));

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
        $this->driver->push(new QueueMessage(new TestJob(['rabbit']))->setChannel($other));

        self::assertNull($this->driver->pop());
        self::assertNotNull($this->driver->pop($other));

        $this->driver->getChannel()->queue_delete($other);
    }

    #[Test]
    public function releaseKeepsAttemptCountedByPop(): void
    {
        $id = $this->driver->push(new QueueMessage(new TestJob(['rabbit'])));

        $message = $this->driver->pop();
        $message->setDelay(0);
        $this->driver->release($message);

        $message = $this->driver->pop();

        self::assertSame($id, $message->getId());
        self::assertSame(2, $message->getAttempts());
    }

    #[Test]
    public function deferDoesNotAddAttemptAndKeepsPreviousFailures(): void
    {
        $this->driver->push(new QueueMessage(new TestJob(['rabbit'])));

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
        $message = new QueueMessage(new TestJob(['rabbit']));
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
        $message = new QueueMessage(new TestJob(['rabbit']), [], 1);
        $this->driver->push($message);

        self::assertNull($this->driver->pop());
        self::assertSame(1, $this->driver->size());

        sleep(2);

        $message = $this->driver->pop();

        self::assertNotNull($message);
        self::assertSame(1, $message->getAttempts());
    }

    #[Test]
    public function deferAppliesDelay(): void
    {
        $this->driver->push(new QueueMessage(new TestJob(['rabbit'])));

        $message = $this->driver->pop();
        $message->setDelay(60);
        $this->driver->defer($message);

        self::assertNull($this->driver->pop());
        self::assertSame(1, $this->driver->size());
    }

    #[Test]
    public function unackedMessageIsRedeliveredAndCountedAfterWorkerDies(): void
    {
        $this->driver->push(new QueueMessage(new TestJob(['rabbit'])));
        $this->driver->pop();

        // Simulates a crashed worker: the connection drops without ack.
        $this->driver->close();

        $message = $this->driver->pop();

        self::assertSame(2, $message->getAttempts());
    }
}
