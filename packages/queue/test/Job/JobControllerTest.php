<?php

declare(strict_types=1);

namespace Windwalker\Queue\Test\Job;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Queue\Attributes\JobEntry;
use Windwalker\Queue\Attributes\JobMiddleware;
use Windwalker\Queue\Attributes\JobMiddlewaresProvider;
use Windwalker\Queue\Exception\AbandonedException;
use Windwalker\Queue\Exception\DeferredException;
use Windwalker\Queue\Exception\RetryAfterException;
use Windwalker\Queue\Job\JobController;
use Windwalker\Queue\Middleware\QueueMiddlewareInterface;
use Windwalker\Queue\QueueMessage;
use Windwalker\Queue\Test\Stub\TestJob;

class JobControllerTest extends TestCase
{
    #[Test]
    public function runInvokesInvokableJob(): void
    {
        $job = new TestJob(['hello']);
        $controller = new QueueMessage($job)->makeJobController();

        self::assertSame($controller, $controller->run());
        self::assertSame(['hello'], $job->executed);
        self::assertFalse($controller->failed);
    }

    #[Test]
    public function runRejectsJobWithoutEntry(): void
    {
        $controller = new QueueMessage()->setJob(new \stdClass())->makeJobController();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageIsOrContains('must have a method with');

        $controller->run();
    }

    #[Test]
    public function runCanBeRepeatedAfterCompletion(): void
    {
        $count = 0;
        $job = new class ($count) {
            public function __construct(public int &$count) {}

            public function __invoke(): void
            {
                $this->count++;
            }
        };
        $controller = new QueueMessage($job)->makeJobController();

        $controller->run();
        $controller->run();

        self::assertSame(2, $count);
    }

    #[Test]
    public function customInvokerWrapsJobInvocation(): void
    {
        $job = new TestJob(['x']);
        $calls = 0;
        $controller = new QueueMessage($job)->makeJobController(
            function (JobController $c, callable $invokable, array $args = []) use (&$calls) {
                $calls++;

                return $invokable($c, ...$args);
            }
        );

        $controller->run();

        self::assertGreaterThanOrEqual(1, $calls);
        self::assertSame(['x'], $job->executed);
    }

    #[Test]
    public function exposesMessageStateThroughHooks(): void
    {
        $message = new QueueMessage(new TestJob(), ['k' => 'v'], 9)->setId('id-1')->setAttempts(2);
        $controller = $message->makeJobController();

        self::assertSame(2, $controller->attempts);
        self::assertSame('id-1', $controller->id);
        self::assertSame(9, $controller->delay);
        self::assertFalse($controller->deleted);
        self::assertSame(['k' => 'v'], $controller->body['data']);
        self::assertSame($message->getJob(), $controller->job);
    }

    #[Test]
    public function failedAcceptsStringOrThrowableAndSuccessClearsIt(): void
    {
        $controller = new QueueMessage(new TestJob())->makeJobController();

        $e = $controller->failed('boom', 3);

        self::assertInstanceOf(\RuntimeException::class, $e);
        self::assertSame(3, $e->getCode());
        self::assertTrue($controller->failed);

        $custom = new \LogicException('x');
        self::assertSame($custom, $controller->failed($custom));

        $controller->success();
        self::assertFalse($controller->failed);
    }

    #[Test]
    public function abandonedMarksJobForDeletionUntilKept(): void
    {
        $controller = new QueueMessage(new TestJob())->makeJobController();

        self::assertTrue($controller->willRetry);

        $e = $controller->abandoned('no more');
        self::assertSame('no more', $e->getMessage());
        self::assertTrue($controller->shouldDelete);
        self::assertFalse($controller->willRetry);

        $controller->keep();
        self::assertNull($controller->abandoned);
        self::assertTrue($controller->willRetry);
    }

    #[Test]
    public function abandonedWrapsThrowable(): void
    {
        $controller = new QueueMessage(new TestJob())->makeJobController();
        $previous = new \LogicException('why');

        $e = $controller->abandoned($previous);

        self::assertSame($previous, $e->getPrevious());
    }

    #[Test]
    public function maxAttemptsExceededMarksJobForDeletion(): void
    {
        $controller = new QueueMessage(new TestJob())->makeJobController();

        $controller->maxAttemptsExceeds = true;

        self::assertTrue($controller->shouldDelete);
    }

    #[Test]
    public function deferStoresDelayAndResumeClearsIt(): void
    {
        $controller = new QueueMessage(new TestJob())->makeJobController();

        $e = $controller->defer(60, 'later');

        self::assertSame(60, $e->delay);
        self::assertSame($e, $controller->defer);

        self::assertSame($controller, $controller->resume());
        self::assertNull($controller->defer);
    }

    #[Test]
    public function retryAfterStoresDelayAndResumeClearsIt(): void
    {
        $controller = new QueueMessage(new TestJob())->makeJobController();

        self::assertNull($controller->retryAfter);
        self::assertInstanceOf(RetryAfterException::class, $controller->retryAfter(30));
        self::assertSame(30, $controller->retryAfter);
        self::assertFalse($controller->failed);

        $controller->resume();
        self::assertNull($controller->retryAfter);
    }

    #[Test]
    public function retryAfterCanBeThrownToStopJobExecution(): void
    {
        $job = new class () {
            public bool $continued = false;

            public function __invoke(JobController $controller): void
            {
                throw $controller->retryAfter(30);

                $this->continued = true;
            }
        };
        $controller = new QueueMessage($job)->makeJobController()->run();

        self::assertFalse($job->continued);
        self::assertSame(30, $controller->retryAfter);
        self::assertFalse($controller->failed);
    }

    #[Test]
    public function logWritesEachMessageToLogger(): void
    {
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $logger->expects($this->exactly(2))->method('log');
        $controller = new QueueMessage(new TestJob())->makeJobController(null, $logger);

        self::assertSame($controller, $controller->log(['a', 'b']));
    }

    #[Test]
    public function deferredExceptionInJobRecordsDeferAndPreviousFailure(): void
    {
        $job = new class () {
            public function __invoke(): void
            {
                DeferredException::throwFrom(30, new \LogicException('busy'));
            }
        };

        $controller = new QueueMessage($job)->makeJobController()->run();

        self::assertSame(30, $controller->defer->delay);
        self::assertSame('busy', $controller->exception->getMessage());
    }

    #[Test]
    public function abandonedExceptionInJobRecordsAbandonedAndPreviousFailure(): void
    {
        $job = new class () {
            public function __invoke(): void
            {
                AbandonedException::throwFrom(new \LogicException('give up'));
            }
        };

        $controller = new QueueMessage($job)->makeJobController()->run();

        self::assertTrue($controller->shouldDelete);
        self::assertSame('give up', $controller->abandoned->getMessage());
        self::assertSame('give up', $controller->exception->getMessage());
    }

    #[Test]
    public function jobEntryAttributeMethodRunsInsideMiddlewareMethods(): void
    {
        $job = new class () {
            public array $order = [];

            #[JobMiddleware(2)]
            public function second(JobController $controller): JobController
            {
                $this->order[] = 'second';

                return $controller->next();
            }

            #[JobMiddleware(1)]
            public function first(JobController $controller): JobController
            {
                $this->order[] = 'first';

                return $controller->next();
            }

            #[JobEntry]
            public function handle(): void
            {
                $this->order[] = 'handle';
            }
        };

        new QueueMessage()->setJob($job)->makeJobController()->run();

        self::assertSame(['first', 'second', 'handle'], $job->order);
    }

    #[Test]
    public function middlewareReturningNullIsRejected(): void
    {
        $controller = new QueueMessage(new TestJob())->makeJobController();
        $ref = new \ReflectionMethod($controller, 'invokeMiddleware');

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageIsOrContains('Middleware returns null');

        $ref->invoke($controller, static fn() => null);
    }

    #[Test]
    public function invalidMiddlewareEntryIsRejected(): void
    {
        $controller = new QueueMessage(new TestJob())->makeJobController();
        $ref = new \ReflectionMethod($controller, 'invokeMiddleware');

        $this->expectException(\InvalidArgumentException::class);

        $ref->invoke($controller, 'not-a-middleware');
    }

    #[Test]
    public function nextRejectsCallsOutsideMiddlewareContext(): void
    {
        $controller = new QueueMessage(new TestJob())->makeJobController();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageIsOrContains('already in pending context');

        $controller->next();
    }

    #[Test]
    public function runCannotBeRepeatedAfterJobThrew(): void
    {
        $job = new class () {
            public function __invoke(): void
            {
                throw new \LogicException('boom');
            }
        };
        $controller = new QueueMessage($job)->makeJobController();

        try {
            $controller->run();
            self::fail('Job exception should bubble up.');
        } catch (\LogicException) {
            // The context is left in the failed state on purpose.
        }

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageIsOrContains('already in middleware context, cannot run again');

        $controller->run();
    }

    #[Test]
    public function middlewaresFromProviderRunAroundTheJob(): void
    {
        $order = [];
        $middleware = new class ($order) implements QueueMiddlewareInterface {
            public function __construct(private array &$order) {}

            public function process(JobController $controller): JobController
            {
                $this->order[] = 'object';

                return $controller->next();
            }
        };

        $job = new class ($middleware, $order) {
            public function __construct(private object $middleware, private array &$order) {}

            #[JobMiddlewaresProvider]
            public function middlewares(): array
            {
                return [
                    $this->middleware,
                    function (JobController $controller) {
                        $this->order[] = 'closure';

                        return $controller->next();
                    },
                ];
            }

            #[JobEntry]
            public function handle(): void
            {
                $this->order[] = 'handle';
            }
        };

        new QueueMessage()->setJob($job)->makeJobController()->run();

        self::assertCount(3, $order);
        self::assertSame('handle', $order[2]);
        self::assertEqualsCanonicalizing(['object', 'closure'], array_slice($order, 0, 2));
    }

    #[Test]
    public function middlewareProviderMustReturnIterable(): void
    {
        $job = new class () {
            #[JobMiddlewaresProvider]
            public function middlewares(): string
            {
                return 'nope';
            }

            #[JobEntry]
            public function handle(): void {}
        };

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageIsOrContains('must return an iterable of middleware list, string given');

        new QueueMessage()->setJob($job)->makeJobController()->run();
    }

    #[Test]
    public function middlewareReturningThrowableIsThrown(): void
    {
        $controller = new QueueMessage(new TestJob())->makeJobController();
        $ref = new \ReflectionMethod($controller, 'invokeMiddleware');

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageIsOrContains('from middleware');

        $ref->invoke($controller, static fn() => new \DomainException('from middleware'));
    }

    #[Test]
    public function jobMarkingControllerFailedThrowsItsException(): void
    {
        $job = new class () {
            public function __invoke(JobController $controller): void
            {
                $controller->failed('marked failed');
            }
        };

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageIsOrContains('marked failed');

        new QueueMessage($job)->makeJobController()->run();
    }

    #[Test]
    public function nestedControllerMiddlewareContinuesItsOwnChain(): void
    {
        $controller = new QueueMessage(new TestJob())->makeJobController();
        $inner = $this->createMock(JobController::class);
        $inner->expects($this->once())->method('next')->willReturnSelf();

        $ref = new \ReflectionMethod($controller, 'invokeMiddleware');

        self::assertSame($inner, $ref->invoke($controller, $inner));
    }

    #[Test]
    public function invalidObjectMiddlewareEntryIsRejectedWithItsType(): void
    {
        $controller = new QueueMessage(new TestJob())->makeJobController();
        $ref = new \ReflectionMethod($controller, 'invokeMiddleware');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('stdClass');

        $ref->invoke($controller, new \stdClass());
    }
}
