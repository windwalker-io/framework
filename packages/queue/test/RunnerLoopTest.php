<?php

declare(strict_types=1);

namespace Windwalker\Queue\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Queue\Attributes\JobFailed;
use Windwalker\Queue\Driver\ChannelAwareDriverInterface;
use Windwalker\Queue\Driver\NullQueueDriver;
use Windwalker\Queue\Event\DebugOutputEvent;
use Windwalker\Queue\Exception\UnrecoverableException;
use Psr\Log\AbstractLogger;
use Windwalker\DI\Definition\ObjectBuilderDefinition;
use Windwalker\Queue\Exception\AbandonedException;
use Windwalker\Queue\Exception\DeferredException;
use Windwalker\Queue\Event\JobFailureEvent;
use Windwalker\Queue\Job\JobController;
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

    #[Test]
    public function workerConvertsEnqueuerJobResultToMessageAndProcessesIt(): void
    {
        $driver = $this->createMock(QueueDriverInterface::class);
        $driver->expects($this->never())->method('pop');
        $driver->expects($this->once())->method('delete')->willReturnSelf();
        $queue = new Queue($driver);
        $enqueuer = new Enqueuer($queue);
        $enqueuer->channel('default', fn() => new TestJob());
        $worker = new Worker($queue);
        $worker->enqueuer = $enqueuer;

        $worker->next('default');

        self::assertFalse($worker->isStop());
    }

    #[Test]
    public function getMessageByJobCreatesJobFromInvokableClassName(): void
    {
        $queue = new Queue(new NullQueueDriver());

        $message = $queue->getMessageByJob(TestJob::class);

        self::assertSame(TestJob::class, $message->getData()['class']);
    }

    #[Test]
    public function processTreatsControllerMarkedFailedAsFailure(): void
    {
        $driver = $this->createMock(QueueDriverInterface::class);
        $driver->expects($this->once())->method('release')->willReturnSelf();
        $driver->expects($this->never())->method('defer');
        $worker = new Worker(new Queue($driver));
        $failures = [];
        $worker->on(
            \Windwalker\Queue\Event\JobFailureEvent::class,
            function (\Windwalker\Queue\Event\JobFailureEvent $e) use (&$failures) {
                $failures[] = $e->exception->getMessage();
            }
        );
        $message = new QueueMessage(
            fn() => throw new \Windwalker\Queue\Exception\DeferredException(
                5,
                'defer',
                0,
                new \RuntimeException('inner failure')
            )
        )->setId(9);

        $worker->process($message);

        self::assertSame(['inner failure'], $failures);
    }

    #[Test]
    public function processDeletesMessageWhenJobThrowsAbandonedException(): void
    {
        $driver = $this->createMock(QueueDriverInterface::class);
        $driver->expects($this->once())->method('delete')->willReturnSelf();
        $driver->expects($this->never())->method('release');
        $worker = new Worker(new Queue($driver));
        $message = new QueueMessage(fn() => throw new AbandonedException('give up'))->setId(1);

        $worker->process($message);
    }

    #[Test]
    public function processDeletesMessageWhenJobCallsAbandonedWithoutThrowing(): void
    {
        $driver = $this->createMock(QueueDriverInterface::class);
        $driver->expects($this->once())->method('delete')->willReturnSelf();
        $driver->expects($this->never())->method('release');
        $worker = new Worker(new Queue($driver));
        $message = new QueueMessage(fn(JobController $c) => $c->abandoned('not needed'))->setId(1);

        $worker->process($message);
    }

    #[Test]
    public function processDeletesAbandonedFailureAndReportsNoRetry(): void
    {
        $driver = $this->createMock(QueueDriverInterface::class);
        $driver->expects($this->once())->method('delete')->willReturnSelf();
        $driver->expects($this->never())->method('release');
        $worker = new Worker(new Queue($driver));
        $events = [];
        $worker->on(JobFailureEvent::class, function (JobFailureEvent $e) use (&$events) {
            $events[] = [$e->backoff, $e->controller->abandoned?->getMessage()];
        });
        $message = new QueueMessage(
            fn() => throw new AbandonedException('bad data', 0, new \RuntimeException('inner'))
        )->setId(1);

        $worker->process($message);

        self::assertSame([[false, 'bad data']], $events);
    }

    #[Test]
    public function processDefersMessageWhenJobThrowsDeferredException(): void
    {
        $driver = $this->createMock(QueueDriverInterface::class);
        $driver->expects($this->once())->method('defer')->willReturnSelf();
        $driver->expects($this->never())->method('delete');
        $worker = new Worker(new Queue($driver));
        $message = new QueueMessage(fn() => throw new DeferredException(20))->setId(1);

        $worker->process($message);

        self::assertSame(20, $message->getDelay());
    }

    #[Test]
    public function processInvokesJobFailedMethodsOnFailure(): void
    {
        $driver = $this->createStub(QueueDriverInterface::class);
        $driver->method('release')->willReturnSelf();
        $worker = new Worker(new Queue($driver));
        $job = new class () {
            public static bool $failedCalled = false;

            public function __invoke(): void
            {
                throw new \RuntimeException('x');
            }

            #[JobFailed]
            public function onFailed(): void
            {
                self::$failedCalled = true;
            }
        };
        $message = new QueueMessage($job)->setId(1);

        $worker->process($message);

        self::assertTrue($job::$failedCalled);
    }

    #[Test]
    public function loopStopsInstantlyWhenCycleThrowsUnrecoverableException(): void
    {
        $driver = $this->createStub(QueueDriverInterface::class);
        $driver->method('pop')->willThrowException(new UnrecoverableException('db is gone'));
        $worker = new class (new Queue($driver), new RunnerOptions(sleep: 0)) extends Worker {
            public array $stops = [];

            public function stop(string $reason = 'Unknown reason', int $code = 0, bool $instant = false): void
            {
                $this->stops[] = [$reason, $code, $instant];

                // The real instant stop calls exit(), which would kill the test process.
                parent::stop($reason, $code);
            }
        };

        $worker->loop('default');

        self::assertSame([['[STOP] Unrecoverable error: db is gone', 1, true]], $worker->stops);
    }

    #[Test]
    public function getAllChannelsAndWildcardUseChannelAwareDriver(): void
    {
        $driver = new class () extends NullQueueDriver implements ChannelAwareDriverInterface {
            public function getChannels(): iterable
            {
                yield from ['a', 'b', 'c'];
            }
        };
        $worker = new Worker(new Queue($driver));
        $sorted = new \ReflectionMethod($worker, 'getSortedChannels');

        self::assertSame(['a', 'b', 'c'], [...$worker->getAllChannels()]);
        self::assertSame(['a', 'b', 'c'], $sorted->invoke($worker, '*'));
        self::assertSame(['x'], $sorted->invoke($worker, 'x'));
    }

    #[Test]
    public function shuffleChannelsKeepsAllChannels(): void
    {
        $worker = new Worker(new Queue(new NullQueueDriver()), new RunnerOptions(shuffleChannels: true));
        $sorted = new \ReflectionMethod($worker, 'getSortedChannels');

        $channels = $sorted->invoke($worker, ['a', 'b', 'c', 'd']);

        self::assertEqualsCanonicalizing(['a', 'b', 'c', 'd'], $channels);
    }

    #[Test]
    public function timeoutSignalStopsRunner(): void
    {
        if (!extension_loaded('pcntl') || !extension_loaded('posix')) {
            self::markTestSkipped('pcntl and posix are required.');
        }

        $worker = new Worker(new Queue(new NullQueueDriver()), new RunnerOptions(timeout: 30, sleep: 0));
        $register = new \ReflectionMethod($worker, 'registerSignals');
        $stops = [];
        $worker->on(\Windwalker\Queue\Event\StopEvent::class, function ($e) use (&$stops) {
            $stops[] = $e->reason;
        });

        try {
            $register->invoke($worker);

            posix_kill(getmypid(), SIGALRM);
            pcntl_signal_dispatch();
        } finally {
            pcntl_alarm(0);
            pcntl_signal(SIGALRM, SIG_DFL);
            pcntl_signal(SIGINT, SIG_DFL);
            pcntl_signal(SIGTERM, SIG_DFL);
        }

        self::assertTrue($worker->isStop());
        self::assertStringStartsWith('A loop process over the max timeout: 30', $stops[0]);
    }

    #[Test]
    public function terminateSignalsRequestGracefulExit(): void
    {
        if (!extension_loaded('pcntl') || !extension_loaded('posix')) {
            self::markTestSkipped('pcntl and posix are required.');
        }

        $worker = new Worker(new Queue(new NullQueueDriver()), new RunnerOptions(timeout: 0));
        $register = new \ReflectionMethod($worker, 'registerSignals');

        try {
            $register->invoke($worker);

            posix_kill(getmypid(), SIGTERM);
            pcntl_signal_dispatch();
        } finally {
            pcntl_signal(SIGINT, SIG_DFL);
            pcntl_signal(SIGTERM, SIG_DFL);
        }

        self::assertTrue($worker->isStop());
    }

    #[Test]
    public function controllerLoggerStripsTagsForLoggerAndEmitsDebugEvent(): void
    {
        $logs = [];
        $logger = new class ($logs) extends AbstractLogger {
            public function __construct(private array &$logs) {}

            public function log($level, string|\Stringable $message, array $context = []): void
            {
                $this->logs[] = [$level, (string) $message];
            }
        };
        $worker = new Worker(new Queue(new NullQueueDriver()), logger: $logger);
        $events = [];
        $worker->on(DebugOutputEvent::class, function (DebugOutputEvent $e) use (&$events) {
            $events[] = [$e->level, $e->message, $e->context];
        });

        $controllerLogger = new \ReflectionMethod($worker, 'createControllerLogger');
        $controllerLogger->invoke($worker)->warning('<info>hello</info>', ['k' => 'v']);

        self::assertSame([['warning', 'hello']], $logs);
        self::assertSame([['warning', '<info>hello</info>', ['k' => 'v']]], $events);
    }

    #[Test]
    public function setInvokerReplacesDefaultInvoker(): void
    {
        $worker = new Worker(new Queue(new NullQueueDriver()));
        $invoker = static fn() => 'custom';

        $worker->setInvoker($invoker);

        self::assertSame($invoker, $worker->getInvoker());
    }

    #[Test]
    public function queueCreatesJobFromDiDefinitionThroughObjectBuilder(): void
    {
        $queue = new Queue(new NullQueueDriver());
        $definition = new ObjectBuilderDefinition(TestJob::class);
        $received = null;

        // The default builder only accepts class names, a DI container supplies a builder that resolves definitions.
        $queue->getObjectBuilder()->setBuilder(
            function ($def) use (&$received) {
                $received = $def;

                return new TestJob(['from-di']);
            }
        );

        $message = $queue->getMessageByJob($definition);

        self::assertSame($definition, $received);
        self::assertInstanceOf(TestJob::class, $message->getJob());
    }
}
