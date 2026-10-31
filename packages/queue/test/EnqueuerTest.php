<?php

declare(strict_types=1);

namespace Windwalker\Queue\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Queue\Driver\NullQueueDriver;
use Windwalker\Queue\Driver\QueueDriverInterface;
use Windwalker\Queue\Enqueuer;
use Windwalker\Queue\Enqueuer\EnqueuerController;
use Windwalker\Queue\Event\AfterEnqueueEvent;
use Windwalker\Queue\Event\BeforeEnqueueEvent;
use Windwalker\Queue\Event\EnqueueFailureEvent;
use Windwalker\Queue\Queue;
use Windwalker\Queue\RunnerOptions;

class EnqueuerTest extends TestCase
{
    #[Test]
    public function enqueueReturnsNullWhenNoHandlerRegistered(): void
    {
        $enqueuer = new Enqueuer(new Queue(new NullQueueDriver()));

        self::assertNull($enqueuer->enqueue('default'));
    }

    #[Test]
    public function enqueueRunsChannelHandlerAndEmitsEvents(): void
    {
        $enqueuer = new Enqueuer(new Queue(new NullQueueDriver()));
        $enqueuer->channel('foo', fn(EnqueuerController $c) => 'result:' . $c->channel);
        $events = [];
        $enqueuer->on(BeforeEnqueueEvent::class, function () use (&$events) {
            $events[] = 'before';
        });
        $enqueuer->on(AfterEnqueueEvent::class, function (AfterEnqueueEvent $e) use (&$events) {
            $events[] = 'after:' . $e->result;
        });

        self::assertSame('result:foo', $enqueuer->enqueue('foo'));
        self::assertSame(['before', 'after:result:foo'], $events);
    }

    #[Test]
    public function enqueueFallsBackToDefaultHandler(): void
    {
        $enqueuer = new Enqueuer(new Queue(new NullQueueDriver()));
        $enqueuer->default(fn(EnqueuerController $c) => 'default:' . $c->channel);

        self::assertSame('default:bar', $enqueuer->enqueue('bar'));
    }

    #[Test]
    public function enqueueEmitsFailureEventAndReturnsNullWhenHandlerThrows(): void
    {
        $enqueuer = new Enqueuer(new Queue(new NullQueueDriver()));
        $enqueuer->channel('foo', fn() => throw new \RuntimeException('boom'));
        $errors = [];
        $enqueuer->on(EnqueueFailureEvent::class, function (EnqueueFailureEvent $e) use (&$errors) {
            $errors[] = $e->exception->getMessage();
        });

        self::assertNull($enqueuer->enqueue('foo'));
        self::assertSame(['boom'], $errors);
    }

    #[Test]
    public function handlerCanPushJobToQueueThroughController(): void
    {
        $driver = $this->createMock(QueueDriverInterface::class);
        $driver->expects($this->once())->method('push')->willReturn('7');
        $enqueuer = new Enqueuer(new Queue($driver));
        $enqueuer->channel('foo', fn(EnqueuerController $c) => $c->enqueue(fn() => 1));

        self::assertSame('7', $enqueuer->enqueue('foo'));
    }

    #[Test]
    public function nextStopsWhenNothingEnqueuedAndStopWhenEmptyEnabled(): void
    {
        $enqueuer = new Enqueuer(new Queue(new NullQueueDriver()), new RunnerOptions(stopWhenEmpty: true));

        $enqueuer->next('default');

        self::assertTrue($enqueuer->isStop());
    }

    #[Test]
    public function nextKeepsRunningWhenHandlerReturnsValue(): void
    {
        $enqueuer = new Enqueuer(new Queue(new NullQueueDriver()), new RunnerOptions(stopWhenEmpty: true));
        $enqueuer->channel('foo', fn() => 'ok');

        $enqueuer->next('foo');

        self::assertFalse($enqueuer->isStop());
    }

    #[Test]
    public function maxRunsCountsEnqueueCalls(): void
    {
        $enqueuer = new Enqueuer(new Queue(new NullQueueDriver()), new RunnerOptions(maxRuns: 1));
        $enqueuer->channel('foo', fn() => 'ok');

        $enqueuer->next('foo');
        $enqueuer->stopIfNecessary();

        self::assertTrue($enqueuer->isStop());
    }

    #[Test]
    public function getAllChannelsReturnsRegisteredChannelNames(): void
    {
        $enqueuer = new Enqueuer(new Queue(new NullQueueDriver()));
        $enqueuer->setChannelHandlers(['a' => fn() => 1, 'b' => fn() => 2]);

        self::assertSame(['a', 'b'], $enqueuer->getAllChannels());
        self::assertSame('Enqueuer', $enqueuer->getRunnerName());
    }

    #[Test]
    public function wildcardChannelRunsAllRegisteredHandlers(): void
    {
        $enqueuer = new Enqueuer(new Queue(new NullQueueDriver()));
        $ran = [];
        $enqueuer->channel('a', function () use (&$ran) {
            $ran[] = 'a';

            return 1;
        });
        $enqueuer->channel('b', function () use (&$ran) {
            $ran[] = 'b';

            return 1;
        });

        $enqueuer->next('*');

        self::assertSame(['a', 'b'], $ran);
    }

    #[Test]
    public function controllerFactoryCreatesCustomController(): void
    {
        $custom = null;
        $enqueuer = new Enqueuer(
            new Queue(new NullQueueDriver()),
            new RunnerOptions(
                controllerFactory: function (string $channel, callable $invoker, $logger) use (&$custom) {
                    return $custom = new EnqueuerController($channel, $invoker(...), $logger);
                }
            )
        );
        $enqueuer->channel('foo', fn() => 'x');

        $enqueuer->enqueue('foo');

        self::assertInstanceOf(EnqueuerController::class, $custom);
        self::assertSame('foo', $custom->channel);
    }
}
