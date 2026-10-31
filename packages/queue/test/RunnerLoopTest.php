<?php

declare(strict_types=1);

namespace Windwalker\Queue\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Queue\Driver\NullQueueDriver;
use Windwalker\Queue\Driver\QueueDriverInterface;
use Windwalker\Queue\Enqueuer;
use Windwalker\Queue\Event\LoopEndEvent;
use Windwalker\Queue\Event\LoopFailureEvent;
use Windwalker\Queue\Event\LoopStartEvent;
use Windwalker\Queue\Queue;
use Windwalker\Queue\QueueMessage;
use Windwalker\Queue\RunnerOptions;
use Windwalker\Queue\Test\Stub\TestJob;
use Windwalker\Queue\Worker;

/**
 * Covers AbstractRunner loop/stop behavior and Worker/Enqueuer integration without a database.
 */
class RunnerLoopTest extends TestCase
{
    #[Test]
    public function loopEmitsLifecycleEventsAndFailureEventWhenCycleThrows(): void
    {
        $driver = $this->createStub(QueueDriverInterface::class);
        $driver->method('pop')->willThrowException(new \RuntimeException('pop failed'));
        $worker = new Worker(new Queue($driver), new RunnerOptions(sleep: 0));
        $events = [];
        $worker->on(LoopStartEvent::class, function () use (&$events) {
            $events[] = 'start';
        });
        $worker->on(LoopFailureEvent::class, function (LoopFailureEvent $e) use (&$events) {
            $events[] = 'failure:' . $e->exception->getMessage();
        });
        $worker->on(LoopEndEvent::class, function () use (&$events, $worker) {
            $events[] = 'end';
            $worker->stop();
        });

        $worker->loop('default');

        self::assertSame(['start', 'failure:pop failed', 'end'], $events);
        self::assertNotNull($worker->pid);
    }

    #[Test]
    public function loopStopsWhenMaxRunsReached(): void
    {
        $driver = $this->createStub(QueueDriverInterface::class);
        $driver->method('pop')->willReturnCallback(fn() => new QueueMessage(new TestJob())->setId(1));
        $driver->method('delete')->willReturnSelf();
        $worker = new Worker(new Queue($driver), new RunnerOptions(sleep: 0, maxRuns: 2));

        $worker->loop('default');

        self::assertTrue($worker->isStop());
    }

    #[Test]
    public function stopIfNecessaryStopsOnNewerRestartSignal(): void
    {
        $file = sys_get_temp_dir() . '/queue-restart-' . uniqid();
        file_put_contents($file, (string) (time() + 100));
        $worker = new Worker(new Queue(new NullQueueDriver()), new RunnerOptions(restartSignal: $file));
        $worker->lastRestart = time();

        $worker->stopIfNecessary();
        unlink($file);

        self::assertTrue($worker->isStop());
    }

    #[Test]
    public function stopIfNecessaryIgnoresOlderRestartSignal(): void
    {
        $file = sys_get_temp_dir() . '/queue-restart-' . uniqid();
        file_put_contents($file, (string) (time() - 100));
        $worker = new Worker(new Queue(new NullQueueDriver()), new RunnerOptions(restartSignal: $file));
        $worker->lastRestart = time();

        $worker->stopIfNecessary();
        unlink($file);

        self::assertFalse($worker->isStop());
    }

    #[Test]
    public function stopIfNecessaryStopsWhenLifetimeExceeded(): void
    {
        $worker = new Worker(new Queue(new NullQueueDriver()), new RunnerOptions(lifetime: 10));
        $worker->lastRestart = time() - 11;

        $worker->stopIfNecessary();

        self::assertTrue($worker->isStop());
    }

    #[Test]
    public function stopIfNecessaryStopsWhenShutdownSignalReceived(): void
    {
        $worker = new Worker(new Queue(new NullQueueDriver()));

        $worker->shutdown();
        $worker->stopIfNecessary();

        self::assertSame(Worker::STATE_STOP, $worker->getState());
    }

    #[Test]
    public function workerProcessesMessageReturnedByEnqueuerHandler(): void
    {
        $driver = $this->createMock(QueueDriverInterface::class);
        $driver->expects($this->never())->method('pop');
        $driver->expects($this->once())->method('delete')->willReturnSelf();
        $queue = new Queue($driver);
        $enqueuer = new Enqueuer($queue);
        $job = new TestJob();
        $enqueuer->channel('default', fn() => new QueueMessage($job)->setId(5));
        $worker = new Worker($queue);
        $worker->enqueuer = $enqueuer;

        $worker->next('default');
    }

    #[Test]
    public function workerFallsBackToQueueWhenEnqueuerReturnsNothing(): void
    {
        $driver = $this->createMock(QueueDriverInterface::class);
        $driver->expects($this->once())->method('pop')->with('other')->willReturn(null);
        $queue = new Queue($driver);
        $enqueuer = new Enqueuer($queue);
        $enqueuer->channel('default', fn() => null);
        $worker = new Worker($queue);
        $worker->enqueuer = $enqueuer;
        $worker->enqueuerChannels = ['default'];

        $worker->next('other');

        self::assertFalse($worker->isStop());
    }

    #[Test]
    public function processDefersMessageWhenJobRequestsDefer(): void
    {
        $driver = $this->createMock(QueueDriverInterface::class);
        $driver->expects($this->once())->method('defer')->willReturnSelf();
        $worker = new Worker(new Queue($driver));
        $message = new QueueMessage(
            new class () {
                public function __invoke(\Windwalker\Queue\Job\JobController $controller): void
                {
                    $controller->defer(30);
                }
            }
        )->setId(3);

        $worker->process($message);

        self::assertSame(30, $message->getDelay());
    }

    #[Test]
    public function processPushesFailedMessageAgainWhenItHasNoId(): void
    {
        $driver = $this->createMock(QueueDriverInterface::class);
        $driver->expects($this->once())->method('push')->willReturn('1');
        $driver->expects($this->never())->method('release');
        $worker = new Worker(new Queue($driver));
        $message = new QueueMessage(fn() => throw new \RuntimeException('fail'));

        $worker->process($message);
    }

    #[Test]
    public function createJobControllerUsesControllerFactoryWhenProvided(): void
    {
        $called = null;
        $worker = new Worker(
            new Queue(new NullQueueDriver()),
            new RunnerOptions(
                controllerFactory: function (QueueMessage $message, callable $invoker, $logger) use (&$called) {
                    return $called = $message->makeJobController($invoker(...), $logger);
                }
            )
        );

        $controller = $worker->createJonController(new QueueMessage(fn() => 1));

        self::assertSame($called, $controller);
    }

    #[Test]
    public function queuePushRejectsStringWithoutInvokableClass(): void
    {
        $queue = new Queue(new NullQueueDriver());

        $this->expectException(\InvalidArgumentException::class);

        $queue->push('NotExistsJobClass');
    }
}
