<?php

declare(strict_types=1);

namespace Windwalker\Queue\Test\Attributes;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Queue\Attributes\DefaultChannel;
use Windwalker\Queue\Driver\QueueDriverInterface;
use Windwalker\Queue\Queue;
use Windwalker\Queue\QueueMessage;
use Windwalker\Queue\Test\Stub\TestJob;

#[DefaultChannel('mails')]
class DefaultChannelJobStub extends TestJob {}

class DefaultChannelChildJobStub extends DefaultChannelJobStub {}

class DefaultChannelTest extends TestCase
{
    #[Test]
    public function resolveReturnsChannelFromAttribute(): void
    {
        self::assertSame('mails', DefaultChannel::resolve(new DefaultChannelJobStub()));
    }

    #[Test]
    public function resolveReturnsNullWithoutAttribute(): void
    {
        self::assertNull(DefaultChannel::resolve(new TestJob()));
    }

    #[Test]
    public function pushUsesDefaultChannelWhenNotGiven(): void
    {
        self::assertSame('mails', $this->pushAndGetChannel(new DefaultChannelJobStub()));
    }

    #[Test]
    public function pushPrefersExplicitChannel(): void
    {
        self::assertSame('custom', $this->pushAndGetChannel(new DefaultChannelJobStub(), 'custom'));
    }

    #[Test]
    public function pushUsesDefaultChannelForQueueMessage(): void
    {
        self::assertSame(
            'mails',
            $this->pushAndGetChannel(new QueueMessage(new DefaultChannelJobStub()))
        );
    }

    #[Test]
    public function pushKeepsNullChannelWithoutAttribute(): void
    {
        self::assertNull($this->pushAndGetChannel(new TestJob()));
    }

    private function pushAndGetChannel(mixed $job, ?string $channel = null): ?string
    {
        $driver = $this->createMock(QueueDriverInterface::class);
        $driver->expects($this->once())
            ->method('push')
            ->willReturnCallback(function (QueueMessage $message) use (&$actual) {
                $actual = $message->getChannel();

                return '1';
            });

        new Queue($driver)->push($job, 0, $channel);

        return $actual;
    }
}
