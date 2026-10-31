<?php

declare(strict_types=1);

namespace Windwalker\Queue\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Queue\Driver\ChannelAwareDriverInterface;
use Windwalker\Queue\Driver\NullQueueDriver;
use Windwalker\Queue\Driver\QueueDriverInterface;
use Windwalker\Queue\Job\ClosureJob;
use Windwalker\Queue\Queue;
use Windwalker\Queue\QueueMessage;
use Windwalker\Queue\Test\Stub\TestJob;

/**
 * Covers Queue behavior that does not need a real database, using test doubles for the driver.
 */
class QueueTest extends TestCase
{
    #[Test]
    public function pushPreparesMessageAndDelegatesToDriver(): void
    {
        $driver = $this->createMock(QueueDriverInterface::class);
        $driver->expects($this->once())
            ->method('push')
            ->willReturnCallback(function (QueueMessage $message) {
                self::assertSame(30, $message->getDelay());
                self::assertSame('hello', $message->getChannel());
                self::assertNotNull($message->createdAt);
                self::assertSame(TestJob::class, $message->getData()['class']);
                self::assertNotNull($message->serializedJob);

                return 'id-1';
            });

        $result = new Queue($driver)->push(new TestJob(), 30, 'hello');

        self::assertSame('id-1', $result);
    }

    #[Test]
    public function pushUsesGivenQueueMessageAsIs(): void
    {
        $message = new QueueMessage(new TestJob());
        $driver = $this->createMock(QueueDriverInterface::class);
        $driver->expects($this->once())->method('push')->with($this->identicalTo($message))->willReturn('1');

        new Queue($driver)->push($message, 5, 'foo');

        self::assertSame(5, $message->getDelay());
        self::assertSame('foo', $message->getChannel());
    }

    #[Test]
    public function pushWrapsClosureInClosureJob(): void
    {
        $queue = new Queue(new NullQueueDriver());

        $message = $queue->getMessageByJob(static fn() => null);

        self::assertInstanceOf(ClosureJob::class, $message->getJob());
        self::assertSame(ClosureJob::class, $message->getData()['class']);
        self::assertSame(ClosureJob::class, $message->getName());
    }

    #[Test]
    public function pushRawAcceptsJsonStringAndArray(): void
    {
        $bodies = [];
        $driver = $this->createStub(QueueDriverInterface::class);
        $driver->method('push')->willReturnCallback(function (QueueMessage $message) use (&$bodies) {
            $bodies[] = $message;

            return '1';
        });
        $queue = new Queue($driver);

        $queue->pushRaw('{"a":1}', 10, 'raw');
        $queue->pushRaw(['b' => 2]);

        self::assertSame(['a' => 1, 'channel' => 'raw'], $bodies[0]->getBody());
        self::assertSame(10, $bodies[0]->getDelay());
        self::assertSame('raw', $bodies[0]->getChannel());
        self::assertSame(['b' => 2, 'channel' => null], $bodies[1]->getBody());
    }

    #[Test]
    public function pushRawRejectsInvalidJsonString(): void
    {
        $this->expectException(\JsonException::class);

        new Queue(new NullQueueDriver())->pushRaw('{not json');
    }

    #[Test]
    public function popDelegatesChannelToDriver(): void
    {
        $message = new QueueMessage();
        $driver = $this->createMock(QueueDriverInterface::class);
        $driver->expects($this->once())->method('pop')->with('hello')->willReturn($message);

        self::assertSame($message, new Queue($driver)->pop('hello'));
    }

    #[Test]
    public function deleteMarksMessageDeletedAfterDriverDelete(): void
    {
        $message = new QueueMessage();
        $driver = $this->createMock(QueueDriverInterface::class);
        $driver->expects($this->once())->method('delete')->with($this->identicalTo($message));

        new Queue($driver)->delete($message);

        self::assertTrue($message->isDeleted());
    }

    #[Test]
    public function deleteWrapsRawIdIntoMessage(): void
    {
        $driver = $this->createMock(QueueDriverInterface::class);
        $driver->expects($this->once())
            ->method('delete')
            ->with($this->callback(fn(QueueMessage $m) => $m->getId() === 7));

        new Queue($driver)->delete(7);
    }

    #[Test]
    public function releaseAndDeferApplyDelayBeforeDelegating(): void
    {
        $message = new QueueMessage();
        $driver = $this->createMock(QueueDriverInterface::class);
        $driver->expects($this->once())->method('release')->with($this->identicalTo($message));
        $driver->expects($this->once())->method('defer')->with($this->identicalTo($message));
        $queue = new Queue($driver);

        $queue->release($message, 20);
        self::assertSame(20, $message->getDelay());

        $queue->defer($message, 40);
        self::assertSame(40, $message->getDelay());
    }

    #[Test]
    public function getChannelsDelegatesToChannelAwareDriver(): void
    {
        $driver = $this->createMockForIntersectionOfInterfaces(
            [QueueDriverInterface::class, ChannelAwareDriverInterface::class]
        );
        $driver->expects($this->once())->method('getChannels')->willReturn(['a', 'b']);

        self::assertSame(['a', 'b'], new Queue($driver)->getChannels());
    }

    #[Test]
    public function getChannelsThrowsWhenDriverIsNotChannelAware(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageIs('Driver does not support get channels.');

        new Queue(new NullQueueDriver())->getChannels();
    }

    #[Test]
    public function setDriverReplacesDriverUsedForPush(): void
    {
        $queue = new Queue(new NullQueueDriver());
        $driver = $this->createMock(QueueDriverInterface::class);
        $driver->expects($this->once())->method('push')->willReturn('new');

        self::assertSame($queue, $queue->setDriver($driver));
        self::assertSame($driver, $queue->getDriver());
        self::assertSame('new', $queue->push(new TestJob()));
    }

    #[Test]
    public function releaseAndDeferWrapRawIdInQueueMessage(): void
    {
        $driver = $this->createMock(QueueDriverInterface::class);
        $driver->expects($this->once())
            ->method('release')
            ->willReturnCallback(function (QueueMessage $message) use (&$driver) {
                self::assertSame(7, $message->getId());
                self::assertSame(3, $message->getDelay());

                return $driver;
            });
        $driver->expects($this->once())
            ->method('defer')
            ->willReturnCallback(function (QueueMessage $message) use (&$driver) {
                self::assertSame(8, $message->getId());
                self::assertSame(4, $message->getDelay());

                return $driver;
            });

        $queue = new Queue($driver);
        $queue->release(7, 3);
        $queue->defer(8, 4);
    }

    #[Test]
    public function pushAcceptsJobClassNameWithInvoke(): void
    {
        $driver = $this->createMock(QueueDriverInterface::class);
        $driver->expects($this->once())->method('push')->willReturn('1');

        $result = new Queue($driver)->push(TestJob::class);

        self::assertSame('1', $result);
    }
}
