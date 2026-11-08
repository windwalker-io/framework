<?php

declare(strict_types=1);

namespace Windwalker\Queue\Test;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Queue\Driver\DatabaseQueueDriver;
use Windwalker\Queue\Driver\NullQueueDriver;
use Windwalker\Queue\Driver\QueueDriverInterface;
use Windwalker\Queue\Event\JobFailureEvent;
use Windwalker\Queue\Event\LoopEndEvent;
use Windwalker\Queue\Event\StopEvent;
use Windwalker\Queue\Queue;
use Windwalker\Queue\QueueMessage;
use Windwalker\Queue\RunnerOptions;
use Windwalker\Queue\Test\Stub\TestJob;
use Windwalker\Queue\Worker;
use Windwalker\Test\Traits\DatabaseTestTrait;

use function Windwalker\uid;

/**
 * The WorkerTest class.
 */
class WorkerTest extends TestCase
{
    use DatabaseTestTrait;

    protected ?Worker $instance = null;

    public static array $logs = [];

    /**
     * @see  Worker::loop
     */
    #[Test]
    public function loopExecutesPushedJobOnCustomChannelUntilStopped(): void
    {
        // if (!in_array('closure', stream_get_wrappers(), true)) {
        //     self::markTestSkipped('Closure serialize not supported now.');
        // }

        $this->instance->getQueue()->push(
            static function () {
                file_put_contents(
                    __DIR__ . '/../tmp/test.log',
                    uid()
                );
                static::$logs[] = 'Job executed.';
            },
            0,
            'hello'
        );

        $this->instance->on(LoopEndEvent::class, fn() => $this->instance->stop());

        $this->instance->loop(['default', 'hello'], ['sleep' => 0.1]);

        self::assertEquals(
            'Job executed.',
            static::$logs[0]
        );

        self::$logs = [];
    }

    /**
     * @see  Worker::stopIfNecessary
     */
    #[Test]
    public function stopIfNecessaryStopsWhenStopConditionMet(): void
    {
        $worker = new Worker(new Queue(new NullQueueDriver()), new RunnerOptions(memoryLimit: 0));

        $worker->stopIfNecessary();

        self::assertTrue($worker->isStop());
    }

    #[Test]
    public function stopIfNecessaryDoesNothingWhenNoConditionMet(): void
    {
        // Disable the memory check, since the process memory of a full test run can exceed the default limit.
        $worker = new Worker(new Queue(new NullQueueDriver()), new RunnerOptions(memoryLimit: PHP_INT_MAX));

        $worker->stopIfNecessary();

        self::assertFalse($worker->isStop());
    }

    #[Test]
    public function maxRunsCountsEachProcessedMessageOnce(): void
    {
        $driver = $this->createStub(QueueDriverInterface::class);
        $driver->method('pop')->willReturnCallback(fn() => new QueueMessage(new TestJob())->setId(1));
        $driver->method('delete')->willReturnSelf();
        $worker = new Worker(new Queue($driver), new RunnerOptions(maxRuns: 2, memoryLimit: PHP_INT_MAX));

        $worker->next('default');
        $worker->stopIfNecessary();
        self::assertFalse($worker->isStop());

        $worker->next('default');
        $worker->stopIfNecessary();
        self::assertTrue($worker->isStop());
    }

    #[Test]
    public function nextStopsWhenQueueIsEmptyAndStopWhenEmptyEnabled(): void
    {
        $worker = new Worker(new Queue(new NullQueueDriver()), new RunnerOptions(stopWhenEmpty: true));

        $worker->next('default');

        self::assertTrue($worker->isStop());
    }

    #[Test]
    public function nextKeepsRunningWhenQueueIsEmpty(): void
    {
        $worker = new Worker(new Queue(new NullQueueDriver()));

        $worker->next(['default', 'foo']);

        self::assertFalse($worker->isStop());
    }

    /**
     * @see  Worker::stop
     */
    #[Test]
    public function stopChangesStateToStopped(): void
    {
        $events = [];
        $this->instance->on(StopEvent::class, function (StopEvent $event) use (&$events) {
            $events[] = $event->reason;
        });

        $this->instance->stop('manual');

        self::assertSame(Worker::STATE_STOP, $this->instance->getState());
        self::assertTrue($this->instance->isStop());
        self::assertSame(['manual'], $events);
    }

    /**
     * @see  Worker::runNextJob
     */
    #[Test]
    public function runNextJobProcessesNextMessageFromQueue(): void
    {
        $message = new QueueMessage(new TestJob(['x']))->setId(5);
        $driver = $this->createMock(QueueDriverInterface::class);
        $driver->expects($this->once())->method('pop')->with('default')->willReturn($message);
        $driver->expects($this->once())->method('delete')->with($this->identicalTo($message))->willReturnSelf();

        new Worker(new Queue($driver))->runNextJob('default');
    }

    /**
     * @see  Worker::process
     */
    #[Test]
    public function processInvokesJobOfMessage(): void
    {
        $job = new TestJob(['done']);
        $message = new QueueMessage($job)->setId(9);
        $driver = $this->createMock(QueueDriverInterface::class);
        $driver->expects($this->once())->method('delete')->with($this->identicalTo($message))->willReturnSelf();
        $driver->expects($this->never())->method('release');

        new Worker(new Queue($driver))->process($message);

        self::assertSame(['done'], $job->executed);
        self::assertTrue($message->isDeleted());
    }

    #[Test]
    public function processReleasesFailedMessageWithBackoff(): void
    {
        $job = new class () {
            public function __invoke(): void
            {
                throw new \RuntimeException('boom');
            }
        };
        $message = new QueueMessage($job)->setId(3);
        $failures = [];
        $driver = $this->createMock(QueueDriverInterface::class);
        $driver->expects($this->once())
            ->method('release')
            ->willReturnCallback(function (QueueMessage $m) use ($driver) {
                self::assertSame(3, $m->getId());

                return $driver;
            });
        $driver->expects($this->never())->method('delete');

        $worker = new Worker(new Queue($driver));
        $worker->on(JobFailureEvent::class, function (JobFailureEvent $event) use (&$failures) {
            $failures[] = $event->exception->getMessage();
        });
        $worker->process($message);

        self::assertSame(['boom'], $failures);
    }

    #[Test]
    public function processDeletesMessageWhenMaxAttemptsExceeded(): void
    {
        $job = new TestJob();
        $message = new QueueMessage($job)->setId(4);
        $message->setAttempts(10);
        $driver = $this->createMock(QueueDriverInterface::class);
        $driver->expects($this->atLeastOnce())->method('delete')->willReturnSelf();
        $driver->expects($this->never())->method('release');

        new Worker(new Queue($driver), new RunnerOptions(tries: 2))->process($message);

        self::assertNull($job->executed);
    }

    /**
     * @see  Worker::shutdown
     */
    #[Test]
    public function shutdownStopsWorker(): void
    {
        $this->instance->shutdown();

        self::assertSame(Worker::STATE_EXITING, $this->instance->getState());
        self::assertTrue($this->instance->isStop());

        $this->instance->stopIfNecessary();

        self::assertSame(Worker::STATE_STOP, $this->instance->getState());
    }

    #[Test]
    public function failureCountsAttemptButDeferDoesNot(): void
    {
        $queue = $this->instance->getQueue();
        $queue->push(fn() => throw new \RuntimeException('fail'), 0, 'worker-attempt');
        $worker = new Worker($queue, new RunnerOptions(tries: 10));

        $worker->next('worker-attempt');
        $row = self::$db->select('*')->from('queue_jobs')->where('channel', 'worker-attempt')->get();
        self::assertEquals(1, $row->attempts);

        self::$db->delete('queue_jobs')->where('channel', 'worker-attempt')->execute();

        $queue->push(
            fn(\Windwalker\Queue\Job\JobController $c) => $c->defer(0),
            0,
            'worker-attempt'
        );
        $worker->next('worker-attempt');
        $row = self::$db->select('*')->from('queue_jobs')->where('channel', 'worker-attempt')->get();
        self::assertEquals(0, $row->attempts);

        self::$db->delete('queue_jobs')->where('channel', 'worker-attempt')->execute();
    }

    #[Test]
    public function retryAfterCountsAttemptDelaysAndRespectsTries(): void
    {
        $queue = $this->instance->getQueue();
        $queue->push(
            fn(\Windwalker\Queue\Job\JobController $c) => $c->retryAfter(60),
            0,
            'worker-retry'
        );
        $worker = new Worker($queue, new RunnerOptions(tries: 2));

        $worker->next('worker-retry');
        $row = self::$db->select('*')->from('queue_jobs')->where('channel', 'worker-retry')->get();
        self::assertEquals(1, $row->attempts);
        self::assertNull($row->reserved);
        self::assertGreaterThan(new \DateTimeImmutable('+50 seconds'), new \DateTimeImmutable($row->visibility));

        // Make it visible again, second run reaches tries and the job is dropped.
        self::$db->update('queue_jobs')->set('visibility', new \DateTimeImmutable('-1 second'))
            ->where('channel', 'worker-retry')->execute();
        $worker->next('worker-retry');

        self::assertCount(0, self::$db->select('*')->from('queue_jobs')->where('channel', 'worker-retry')->all());
    }

    #[Before]
    protected function createWorkerInstance(): void
    {
        $this->instance = new Worker(
            new Queue(
                new DatabaseQueueDriver(self::$db)
            )
        );
    }

    /**
     * setupDatabase
     *
     * @return  void
     */
    protected static function setupDatabase(): void
    {
        self::createDatabase('pdo_mysql');
        self::importFromFile(__DIR__ . '/../resources/sql/queue_jobs.sql');
    }
}
