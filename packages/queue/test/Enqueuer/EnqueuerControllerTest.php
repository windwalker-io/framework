<?php

declare(strict_types=1);

namespace Windwalker\Queue\Test\Enqueuer;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Windwalker\Queue\Driver\QueueDriverInterface;
use Windwalker\Queue\Enqueuer\EnqueuerController;
use Windwalker\Queue\Queue;
use Windwalker\Queue\QueueMessage;

class EnqueuerControllerTest extends TestCase
{
    #[Test]
    public function runPassesControllerAndArgsToInvokable(): void
    {
        $controller = new EnqueuerController('foo');

        $result = $controller->run(
            fn(EnqueuerController $c, int $a, int $b) => [$c, $a + $b],
            [1, 2]
        );

        self::assertSame([$controller, 3], $result);
    }

    #[Test]
    public function runUsesCustomInvoker(): void
    {
        $controller = new EnqueuerController('foo', fn($c, $invokable, $args) => 'custom');

        self::assertSame('custom', $controller->run(fn() => 'ignored'));
    }

    #[Test]
    public function logWritesEachMessageToLogger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->exactly(2))->method('log')->with(LogLevel::WARNING);
        $controller = new EnqueuerController('foo', null, $logger);

        self::assertSame($controller, $controller->log(['a', 'b'], LogLevel::WARNING));
    }

    #[Test]
    public function enqueueUsesControllerChannelByDefault(): void
    {
        $driver = $this->createMock(QueueDriverInterface::class);
        $driver->expects($this->exactly(2))
            ->method('push')
            ->willReturnCallback(function (QueueMessage $message) {
                static $channels = [];
                $channels[] = $message->getChannel();

                if (count($channels) === 2) {
                    self::assertSame(['foo', 'bar'], $channels);
                }

                return '1';
            });
        $controller = new EnqueuerController('foo');
        $controller->queue = new Queue($driver);

        self::assertSame('1', $controller->enqueue(new \stdClass()));
        $controller->enqueue(new \stdClass(), 0, 'bar');
    }
}
