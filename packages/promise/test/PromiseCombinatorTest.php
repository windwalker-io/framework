<?php

declare(strict_types=1);

namespace Windwalker\Promise\Test;

use BadMethodCallException;
use Exception;
use PHPUnit\Framework\Attributes\Test;
use Windwalker\Promise\Enum\PromiseState;
use Windwalker\Promise\Exception\AggregateException;
use Windwalker\Promise\Exception\UncaughtException;
use Windwalker\Promise\Promise;

class PromiseCombinatorTest extends AbstractPromiseTestCase
{
    #[Test]
    public function raceSettlesWithFirstSettledValue(): void
    {
        $p = Promise::race([
            new Promise(),
            Promise::resolve('fast'),
        ]);

        self::assertSame('fast', $p->wait());
    }

    #[Test]
    public function raceRejectsWithFirstRejection(): void
    {
        $e = new Exception('fail');

        $this->expectExceptionObject($e);

        Promise::race([Promise::reject($e)])->wait();
    }

    #[Test]
    public function raceOfEmptyArrayResolvesWithNull(): void
    {
        self::assertNull(Promise::race([])->wait());
    }

    #[Test]
    public function anyResolvesWithFirstFulfilledValue(): void
    {
        $p = Promise::any([
            Promise::reject(new Exception('x')),
            Promise::resolve('ok'),
        ]);

        self::assertSame('ok', $p->wait());
    }

    #[Test]
    public function anyRejectsWithAllErrorsWhenEveryPromiseRejects(): void
    {
        $e1 = new Exception('1');
        $e2 = new Exception('2');

        $p = Promise::any([Promise::reject($e1), Promise::reject($e2)]);

        try {
            $p->wait();
            self::fail('Should reject.');
        } catch (\Throwable $e) {
            self::assertSame([$e1, $e2], $e instanceof AggregateException ? $e->getErrors() : $e->getReason());
        }
    }

    #[Test]
    public function anyOfEmptyArrayRejectsWithAggregateException(): void
    {
        try {
            Promise::any([])->wait();
            self::fail('Should reject.');
        } catch (UncaughtException $e) {
            self::assertInstanceOf(AggregateException::class, $e->getPrevious());
            self::assertSame('All promises were rejected', $e->getMessage());
        }
    }

    #[Test]
    public function catchHandlesRejection(): void
    {
        $p = Promise::reject(new Exception('bad'))
            ->catch(static fn(Exception $e) => 'caught ' . $e->getMessage());

        self::assertSame('caught bad', $p->wait());
    }

    #[Test]
    public function doneBehavesLikeThen(): void
    {
        self::assertSame('A!', Promise::resolve('A')->done(static fn($v) => $v . '!')->wait());
    }

    #[Test]
    public function finallyRunsAfterFulfillmentAndKeepsValue(): void
    {
        $ran = false;

        $p = Promise::resolve('V')->finally(function () use (&$ran) {
            $ran = true;
        });

        self::assertSame('V', $p->wait());
        self::assertTrue($ran);
    }

    #[Test]
    public function finallyRunsAfterRejectionAndKeepsReason(): void
    {
        $ran = false;
        $e = new Exception('R');

        $p = Promise::reject($e)->finally(function () use (&$ran) {
            $ran = true;
        });

        try {
            $p->wait();
            self::fail('Should reject.');
        } catch (UncaughtException $caught) {
            self::assertSame($e, $caught->getPrevious());
        }

        self::assertTrue($ran);
    }

    #[Test]
    public function withResolversRejectRejectsPromise(): void
    {
        [$promise, , $reject] = Promise::withResolvers();
        $e = new Exception('rejected');

        $handled = $promise->catch(static fn(Exception $reason) => $reason);

        $reject($e);

        self::assertSame($e, $handled->wait());
        self::assertTrue($promise->isRejected());
    }

    #[Test]
    public function stateHelpersReportCurrentState(): void
    {
        $pending = new Promise();
        $fulfilled = Promise::resolve('x');
        $rejected = Promise::reject(new Exception('x'));
        $rejected->catch(static fn() => null);
        $this->flushAsyncEvents();
        $fulfilled->wait();

        self::assertTrue($pending->isPending());
        self::assertFalse($pending->isSettled());
        self::assertTrue($fulfilled->isFulfilled());
        self::assertTrue($fulfilled->isSettled());
        self::assertSame(PromiseState::FULFILLED, $fulfilled->getState());
        self::assertTrue($rejected->isRejected());
    }

    #[Test]
    public function backwardCompatibleStaticCallsCreatePromises(): void
    {
        self::assertSame('a', Promise::resolved('a')->wait());
        self::assertSame('b', Promise::rejected(new Exception('b'))->catch(fn(Exception $e) => $e->getMessage())->wait());
    }

    #[Test]
    public function unknownInstanceMethodThrowsBadMethodCall(): void
    {
        $this->expectException(BadMethodCallException::class);

        (new Promise())->nope();
    }

    #[Test]
    public function unknownStaticMethodThrowsBadMethodCall(): void
    {
        $this->expectException(BadMethodCallException::class);

        Promise::nope();
    }
}
