<?php

declare(strict_types=1);

namespace Windwalker\Queue\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Queue\QueueMessage;
use Windwalker\Queue\Test\Stub\TestJob;

class QueueMessageTest extends TestCase
{
    #[Test]
    public function constructorAppliesJobDataAndDelay(): void
    {
        $job = new TestJob(['a']);

        $message = new QueueMessage($job, ['foo' => 'bar'], 30);

        self::assertSame($job, $message->getJob());
        self::assertSame(['foo' => 'bar'], $message->getData());
        self::assertSame(30, $message->getDelay());
    }

    #[Test]
    public function ageCountsSecondsSinceCreatedAndSurvivesBodyRoundTrip(): void
    {
        $message = new QueueMessage(new TestJob());

        self::assertSame(0, $message->age);

        $message->setCreatedAt(new \DateTimeImmutable('-90 seconds'));

        self::assertGreaterThanOrEqual(90, $message->age);
        self::assertLessThan(95, $message->age);

        // Drivers that do not store `created` separately (SQS) restore it from the body.
        $copy = new QueueMessage();
        $copy->setBody(json_decode(json_encode($message), true));

        self::assertSame($message->createdAt->getTimestamp(), $copy->createdAt->getTimestamp());
        self::assertGreaterThanOrEqual(90, $copy->age);
    }

    #[Test]
    public function ageDurationIsDurationOnPhp86OrThrows(): void
    {
        $message = new QueueMessage(new TestJob());
        $message->setCreatedAt(new \DateTimeImmutable('-90 seconds'));

        if (class_exists(\Time\Duration::class)) {
            self::assertInstanceOf(\Time\Duration::class, $message->ageDuration);
        } else {
            $this->expectException(\LogicException::class);

            $message->ageDuration;
        }
    }

    #[Test]
    public function totalDequeuesSumsAttemptsAndDeferTimes(): void
    {
        $message = new QueueMessage();

        self::assertSame(0, $message->totalDequeues);

        $message->setAttempts(3);

        self::assertSame(3, $message->totalDequeues);

        $message->setDeferTimes(2);

        self::assertSame(5, $message->totalDequeues);
    }

    #[Test]
    public function getAndSetReadAndWriteBodyValues(): void
    {
        $message = new QueueMessage();

        self::assertSame('default', $message->get('missing', 'default'));
        self::assertSame($message, $message->set('foo', 'bar'));
        self::assertSame('bar', $message->get('foo'));
    }

    #[Test]
    public function channelAndNameAreStoredInBody(): void
    {
        $message = new QueueMessage();

        self::assertNull($message->getChannel());
        self::assertSame('', $message->getName());

        $message->setChannel('hello')->setName('job-name');

        self::assertSame('hello', $message->getChannel());
        self::assertSame('job-name', $message->getName());
        self::assertSame(['channel' => 'hello', 'name' => 'job-name'], $message->getBody());
    }

    #[Test]
    public function getDataDefaultsToEmptyArray(): void
    {
        self::assertSame([], new QueueMessage()->getData());
    }

    #[Test]
    public function setJobDropsPreviouslySerializedJob(): void
    {
        $message = new QueueMessage(new TestJob(['old']));
        $message->serializeJob();

        self::assertNotNull($message->serializedJob);

        $new = new TestJob(['new']);
        $message->setJob($new);

        self::assertNull($message->serializedJob);
        self::assertSame($new, $message->getJob());
    }

    #[Test]
    public function serializeJobIsIdempotent(): void
    {
        $message = new QueueMessage(new TestJob(['a']));

        $message->serializeJob();
        $first = $message->serializedJob;
        $message->serializeJob();

        self::assertSame($first, $message->serializedJob);
    }

    #[Test]
    public function jsonSerializeIncludesSerializedJobWithoutMutatingMessage(): void
    {
        $message = new QueueMessage(new TestJob(['a']), ['x' => 1]);

        $json = $message->jsonSerialize();

        self::assertSame(['x' => 1], $json['data']);
        self::assertSame(['a'], unserialize($json['job'])->logs);
        self::assertNull($message->serializedJob);
    }

    #[Test]
    public function getJobUnserializesJobFromBody(): void
    {
        $message = new QueueMessage();
        $message->setBody(['job' => serialize(new TestJob(['restored']))]);

        self::assertSame(['restored'], $message->getJob()->logs);
    }

    #[Test]
    public function messageSurvivesNativeSerializeRoundTrip(): void
    {
        $message = new QueueMessage(new TestJob(['a']), ['x' => 1], 5);
        $message->setId('abc')->setAttempts(3)->setRawBody('raw')->setDeleted(true)
            ->setCreatedAt('2024-01-02 03:04:05');

        /** @var QueueMessage $copy */
        $copy = unserialize(serialize($message));

        self::assertSame('abc', $copy->getId());
        self::assertSame(3, $copy->getAttempts());
        self::assertSame('raw', $copy->getRawBody());
        self::assertSame(5, $copy->getDelay());
        self::assertTrue($copy->isDeleted());
        self::assertSame(['x' => 1], $copy->getData());
        self::assertSame(['a'], $copy->getJob()->logs);
        self::assertSame('2024-01-02 03:04:05', $copy->createdAt->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function setCreatedAtAcceptsStringDateTimeAndNull(): void
    {
        $message = new QueueMessage();

        $message->setCreatedAt('2024-05-06 07:08:09');
        self::assertSame('2024-05-06 07:08:09', $message->createdAt->format('Y-m-d H:i:s'));

        $date = new \DateTimeImmutable('2020-01-01');
        $message->setCreatedAt($date);
        self::assertSame($date, $message->createdAt);

        $message->setCreatedAt(null);
        self::assertNull($message->createdAt);
    }
}
