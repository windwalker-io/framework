<?php

declare(strict_types=1);

namespace Windwalker\Queue\Test\Driver;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Queue\Driver\InfinityQueueDriver;
use Windwalker\Queue\Driver\NullQueueDriver;
use Windwalker\Queue\Driver\SyncQueueDriver;
use Windwalker\Queue\Failer\NullQueueFailer;
use Windwalker\Queue\Job\NullJob;
use Windwalker\Queue\QueueMessage;
use Windwalker\Queue\Test\Stub\TestJob;

class NullAndSyncQueueDriverTest extends TestCase
{
    #[Test]
    public function nullDriverDiscardsMessages(): void
    {
        $driver = new NullQueueDriver();
        $message = new QueueMessage(new TestJob());

        self::assertSame('0', $driver->push($message));
        self::assertNull($driver->pop('default'));
        self::assertSame($driver, $driver->delete($message));
        self::assertSame($driver, $driver->release($message));
        self::assertSame($driver, $driver->defer($message));
    }

    #[Test]
    public function nullFailerStoresNothing(): void
    {
        $failer = new NullQueueFailer();

        self::assertSame(0, $failer->add('c', 'ch', '{}', \RuntimeException::class));
        self::assertSame([], $failer->all());
        self::assertSame([], $failer->get(1));
        self::assertTrue($failer->remove(1));
        self::assertTrue($failer->clear());
    }

    #[Test]
    public function syncDriverPushRunsHandlerImmediatelyAndReturnsOutputString(): void
    {
        $received = null;
        $driver = new SyncQueueDriver(
            function (QueueMessage $message) use (&$received) {
                $received = $message;

                return 'done';
            }
        );
        $message = new QueueMessage(new TestJob());

        self::assertSame('done', $driver->push($message));
        self::assertSame($message, $received);
    }

    #[Test]
    public function syncDriverCallsDebugCallbackWithHandlerOutput(): void
    {
        $debugged = [];
        $driver = new SyncQueueDriver(
            fn() => 'out',
            ['debug' => function ($output, $d) use (&$debugged) {
                $debugged = [$output, $d];
            }]
        );

        $driver->push(new QueueMessage(new TestJob()));

        self::assertSame('out', $debugged[0]);
        self::assertSame($driver, $debugged[1]);
    }

    #[Test]
    public function syncDriverHandlerCanBeReplaced(): void
    {
        $driver = new SyncQueueDriver(fn() => 'a');
        $handler = fn() => 'b';

        self::assertSame($driver, $driver->setHandler($handler));
        self::assertSame($handler, $driver->getHandler());
        self::assertSame('b', $driver->push(new QueueMessage(new TestJob())));
    }

    #[Test]
    public function syncDriverDoesNotStoreMessages(): void
    {
        $driver = new SyncQueueDriver();
        $message = new QueueMessage(new TestJob());

        self::assertNull($driver->pop());
        self::assertSame($driver, $driver->delete($message));
        self::assertSame($driver, $driver->release($message));
        self::assertSame($driver, $driver->defer($message));
        self::assertInstanceOf(\Closure::class, SyncQueueDriver::getDefaultHandler());
    }

    #[Test]
    public function infinityDriverPopsFreshMessageEveryTime(): void
    {
        $driver = new InfinityQueueDriver(static fn() => null);

        $a = $driver->pop();
        $b = $driver->pop();

        self::assertNotSame('', $a->getId());
        self::assertStringStartsWith('mq__', $a->getId());
        self::assertNotSame($a->getId(), $b->getId());
        self::assertNotNull($a->createdAt);
    }

    #[Test]
    public function infinityDriverPushReturnsMessageIdAndIgnoresStateChanges(): void
    {
        $driver = new InfinityQueueDriver(static fn() => null);
        $message = new QueueMessage(new TestJob())->setId('abc');

        self::assertSame('abc', $driver->push($message));
        self::assertSame('', $driver->push(new QueueMessage(new TestJob())));
        self::assertSame($driver, $driver->delete($message));
        self::assertSame($driver, $driver->release($message));
        self::assertSame($driver, $driver->defer($message));
    }

    #[Test]
    public function syncDriverRunsMessageWithDefaultHandler(): void
    {
        $job = new TestJob(['sync']);
        $driver = new SyncQueueDriver();

        $driver->push(new QueueMessage($job));

        self::assertSame(['sync'], $job->executed);
    }

    #[Test]
    public function syncDriverDebugOptionPassesOutputToCallback(): void
    {
        $outputs = [];
        $driver = new SyncQueueDriver(
            static fn() => 'result',
            ['debug' => function ($output, $self) use (&$outputs) {
                $outputs[] = [$output, $self];
            }]
        );

        $id = $driver->push(new QueueMessage(new TestJob()));

        self::assertSame('result', $id);
        self::assertSame([['result', $driver]], $outputs);
    }

    #[Test]
    public function syncDriverDebugOptionShowsOutputWithoutCallback(): void
    {
        $driver = new SyncQueueDriver(static fn() => 'shown', ['debug' => true]);

        $stream = fopen('php://memory', 'w+b');
        $original = \Windwalker\Utilities\Arr::$output;
        \Windwalker\Utilities\Arr::$output = $stream;

        try {
            $driver->push(new QueueMessage(new TestJob()));
        } finally {
            \Windwalker\Utilities\Arr::$output = $original;
        }

        rewind($stream);
        $output = stream_get_contents($stream);

        self::assertStringContainsString('shown', $output);
    }

    #[Test]
    public function syncDriverHandlerCanBeReplacedAndNeverHoldsMessages(): void
    {
        $driver = new SyncQueueDriver();
        $handler = static fn() => 'other';

        self::assertSame($driver, $driver->setHandler($handler));
        self::assertSame($handler, $driver->getHandler());
        self::assertNull($driver->pop());
    }

    #[Test]
    public function nullJobDoesNothing(): void
    {
        self::assertNull(new NullJob()());
    }
}
