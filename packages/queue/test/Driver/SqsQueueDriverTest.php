<?php

declare(strict_types=1);

namespace Windwalker\Queue\Test\Driver;

use Aws\Sqs\SqsClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Throwable;
use Windwalker\Queue\Driver\SqsQueueDriver;
use Windwalker\Queue\QueueMessage;
use Windwalker\Queue\Test\Stub\TestJob;
use Windwalker\Utilities\Env;

/**
 * Runs against a local SQS-compatible server (ElasticMQ), see `sqs` in docker-compose.dist.yml.
 * Set WINDWALKER_TEST_SQS_ENDPOINT to change the endpoint. Skipped unless SQS_ENABLED is set (e.g. in phpunit.xml).
 */
class SqsQueueDriverTest extends TestCase
{
    private SqsQueueDriver $driver;

    private SqsClient $client;

    private string $channel;

    private string $endpoint;

    protected function setUp(): void
    {
        if (!Env::get('SQS_ENABLED')) {
            self::markTestSkipped('SQS tests are not enabled. Set SQS_ENABLED=1 to run them.');
        }

        $this->endpoint = $endpoint = getenv('WINDWALKER_TEST_SQS_ENDPOINT') ?: 'http://localhost:9324';

        $this->channel = 'wwtest' . bin2hex(random_bytes(4));

        $options = [
            'region' => 'us-east-1',
            'version' => 'latest',
            'endpoint' => $endpoint,
        ];

        try {
            $this->client = new SqsClient($options + ['credentials' => ['key' => 'x', 'secret' => 'x']]);
            $this->client->createQueue(['QueueName' => $this->channel]);
        } catch (Throwable $e) {
            self::markTestSkipped('SQS test server is not available: ' . $e->getMessage());
        }

        $this->driver = new SqsQueueDriver('x', 'x', $this->channel, $options);
    }

    protected function tearDown(): void
    {
        if (isset($this->client, $this->driver)) {
            $this->client->deleteQueue(['QueueUrl' => $this->driver->getQueueUrl()]);
        }
    }

    #[Test]
    public function pushPopAndDeleteRoundTrip(): void
    {
        $id = $this->driver->push(new QueueMessage(new TestJob(['sqs'])));

        $message = $this->driver->pop();

        self::assertSame($id, $message->getId());
        self::assertSame(1, $message->getAttempts());

        $this->driver->delete($message);

        self::assertNull($this->driver->pop());
    }

    #[Test]
    public function releaseKeepsAttemptCountedByPop(): void
    {
        $this->driver->push(new QueueMessage(new TestJob(['sqs'])));

        $message = $this->driver->pop();
        $message->setDelay(0);
        $this->driver->release($message);

        $message = $this->driver->pop();

        self::assertSame(2, $message->getAttempts());
    }

    #[Test]
    public function deferDoesNotAddAttemptAndKeepsPreviousFailures(): void
    {
        $this->driver->push(new QueueMessage(new TestJob(['sqs'])));

        // One failure first
        $message = $this->driver->pop();
        $message->setDelay(0);
        $this->driver->release($message);

        // Run 2 is deferred instead of failing
        $message = $this->driver->pop();
        self::assertSame(2, $message->getAttempts());

        $message->setDelay(0);
        $this->driver->defer($message);

        $message = $this->driver->pop();

        // Same value as the run that was deferred: defer did not count as an attempt.
        self::assertSame(2, $message->getAttempts());
        self::assertSame(1, $message->getDeferTimes());

        $message->setDelay(0);
        $this->driver->defer($message);

        $message = $this->driver->pop();

        self::assertSame(2, $message->getAttempts());
        self::assertSame(2, $message->getDeferTimes());
    }

    #[Test]
    public function createdAtIsKeptAcrossDeferAndRelease(): void
    {
        $message = new QueueMessage(new TestJob(['sqs']));
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
    public function deferAppliesDelay(): void
    {
        $this->driver->push(new QueueMessage(new TestJob(['sqs'])));

        $message = $this->driver->pop();
        $message->setDelay(60);
        $this->driver->defer($message);

        self::assertNull($this->driver->pop());

        // The re-sent copy is held back by the delay instead of being visible.
        $attributes = $this->client->getQueueAttributes(
            [
                'QueueUrl' => $this->driver->getQueueUrl(),
                'AttributeNames' => ['ApproximateNumberOfMessagesDelayed', 'ApproximateNumberOfMessages'],
            ]
        )['Attributes'];

        self::assertSame('1', $attributes['ApproximateNumberOfMessagesDelayed']);
        self::assertSame('0', $attributes['ApproximateNumberOfMessages']);
    }

    #[Test]
    public function queueUrlIsUsedAsIsWhenChannelIsUrl(): void
    {
        $url = $this->driver->getQueueUrl();

        self::assertSame($url, $this->driver->getQueueUrl($url));
    }

    #[Test]
    public function fifoQueueGetsGroupAndDeduplicationIdsAndKeepsAttemptsOnDefer(): void
    {
        $name = $this->channel . '.fifo';
        $this->client->createQueue(['QueueName' => $name, 'Attributes' => ['FifoQueue' => 'true']]);
        $driver = new SqsQueueDriver('x', 'x', $name, ['region' => 'us-east-1', 'version' => 'latest', 'endpoint' => $this->endpoint]);

        try {
            $driver->push(new QueueMessage(new TestJob(['fifo'])));
            $message = $driver->pop();

            self::assertSame(1, $message->getAttempts());

            // Identical body re-sent by defer must not be dropped as a duplicate.
            $driver->defer($message);
            $message = $driver->pop();

            self::assertNotNull($message);
            self::assertSame(1, $message->getAttempts());
        } finally {
            $this->client->deleteQueue(['QueueUrl' => $driver->getQueueUrl()]);
        }
    }
}
