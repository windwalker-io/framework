<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Promise;

use Closure;
use ReflectionFunction;
use ReflectionMethod;
use Throwable;
use TypeError;
use Windwalker\Promise\Enum\PromiseState;
use Windwalker\Promise\Exception\AggregateException;
use Windwalker\Promise\Exception\UncaughtException;
use Windwalker\Promise\Exception\UnsettledException;
use Windwalker\Promise\Scheduler\ScheduleCursor;
use Windwalker\Promise\Scheduler\ScheduleRunner;

use function Windwalker\nope;

/**
 * The Promise class.
 *
 * @since  __DEPLOY_VERSION__
 */
class Promise implements ExtendedPromiseInterface
{
    protected PromiseState $state = PromiseState::PENDING;

    /**
     * @var mixed
     */
    protected mixed $value = null;

    /**
     * @var array<array{ 0: static, 1: callable, 2: ?callable }>
     */
    protected array $children = [];

    /**
     * @var ScheduleCursor
     */
    protected ScheduleCursor $scheduleCursor;

    public ?\Closure $uncaughtLogger = null;

    public static ?\Closure $defaultUncaughtLogger = null;

    protected bool $constructingSynchronously = false;

    protected int $i = 0;

    /**
     * create
     *
     * @param  callable|null  $resolver
     *
     * @return static
     * @throws Throwable
     * @throws \ReflectionException
     */
    public static function create(#[\SensitiveParameter] ?callable $resolver = null): static
    {
        return new static($resolver);
    }

    /**
     * @param  array  $values
     *
     * @return  ExtendedPromiseInterface
     */
    public static function all(array $values): ExtendedPromiseInterface
    {
        return new static(
            static function ($resolve, $reject) use ($values) {
                $count = count($values);
                $done = 0;

                foreach ($values as $i => $value) {
                    static::resolved($value)
                        ->then(
                            static function ($v) use (&$done, &$count, $resolve, $i, &$values) {
                                $values[$i] = $v;
                                $done++;

                                if ($done !== $count) {
                                    return;
                                }

                                $resolve($values);
                            },
                            $reject
                        );
                }
            }
        );
    }

    /**
     * @throws Throwable
     */
    public static function allSettled(array $values): ExtendedPromiseInterface
    {
        return static::all(
            array_map(
                static fn($value) => Promise::resolved($value)
                    ->then(
                        fn($value) => SettledResult::fulfilled($value),
                        fn($value) => SettledResult::rejected($value)
                    ),
                $values
            )
        );
    }

    /**
     * @see https://github.com/tc39/proposal-promise-any
     *
     * @param  array  $values
     *
     * @return  ExtendedPromiseInterface
     */
    public static function any(array $values): ExtendedPromiseInterface
    {
        if ($values === []) {
            return static::rejected(new AggregateException('All promises were rejected'));
        }

        $errors = [];
        $counter = 0;
        $done = false;

        return new static(
            function ($resolve, $reject) use (&$counter, $values, &$done, &$errors) {
                foreach (array_values($values) as $i => $value) {
                    Promise::resolved($value)
                        ->then(
                            function ($v) use (&$done, $resolve) {
                                if (!$done) {
                                    $resolve($v);
                                    $done = true;
                                }

                                return $v;
                            }
                        )
                        ->catch(
                            function ($e) use ($reject, $values, &$counter, &$errors, $i) {
                                $errors[$i] = $e;
                                $counter++;

                                if ($counter === count($values)) {
                                    $reject($errors);
                                }
                            }
                        );
                }
            }
        );
    }

    /**
     * race
     *
     * @param  array  $values
     *
     * @return  ExtendedPromiseInterface
     */
    public static function race(array $values): ExtendedPromiseInterface
    {
        return new static(
            static function ($resolve, $reject) use ($values) {
                if ($values === []) {
                    $resolve();
                }

                foreach ($values as $i => $value) {
                    static::resolved($value)
                        ->then(
                            $resolve,
                            $reject
                        );
                }
            }
        );
    }

    /**
     * @throws Throwable
     */
    public static function try(callable $callback): static
    {
        return static::resolved()->then(fn () => $callback());
    }

    /**
     * Promise constructor.
     *
     * @param  ?callable  $resolver
     *
     * @throws Throwable
     * @throws \ReflectionException
     */
    public function __construct(#[\SensitiveParameter] ?callable $resolver = null)
    {
        $this->scheduleCursor = ScheduleRunner::getInstance()->createCursor();

        $this->constructingSynchronously = true;

        if ($resolver) {
            $this->constructing($resolver);
        }

        $this->constructingSynchronously = false;

        static $i = 0;
        $this->i = ++$i;
    }

    /**
     * @inheritDoc
     */
    public function done(?callable $onFulfilled = null): static
    {
        return $this->then($onFulfilled);
    }

    /**
     * @inheritDoc
     */
    public function catch(?callable $onRejected): static
    {
        return $this->then(null, $onRejected);
    }

    /**
     * @inheritDoc
     */
    public function finally(?callable $onFulfilledOrRejected): static
    {
        return $this->then(
            function () use ($onFulfilledOrRejected) {
                $onFulfilledOrRejected();

                return $this->value;
            },
            function () use ($onFulfilledOrRejected) {
                $onFulfilledOrRejected();

                return static::rejected($this->value);
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function then(?callable $onFulfilled = null, ?callable $onRejected = null): static
    {
        $onFulfilled ??= nope();

        $child = new static();

        // Handle SETTLED
        if ($this->isSettled()) {
            // Self is settled, we push callback to schedule and run after a mini time.
            $this->scheduleFor(
                fn() => $this->settleChild($child, $onFulfilled, $onRejected),
                $child
            );

            return $child;
        }

        // Handle PENDING
        // Self is still pending, we don't know when will be settled,
        // push child and callbacks to waiting list and settle them later.
        $this->children[] = [$child, $onFulfilled, $onRejected];

        return $child;
    }

    /**
     * @param  Promise        $child
     * @param  callable       $onFulfilled
     * @param  callable|null  $onRejected
     *
     * @return  void
     *
     * @throws UncaughtException
     */
    private function settleChild(self $child, callable $onFulfilled, ?callable $onRejected): void
    {
        if ($this->getState()->isPending()) {
            throw new TypeError('Parent should not be pending if settling children.');
        }

        try {
            if ($this->getState() === PromiseState::FULFILLED) {
                $child->resolve($onFulfilled($this->value));
            } elseif ($onRejected) {
                $child->resolve($onRejected($this->value));
            } else {
                $child->reject($this->value);
            }
        } catch (UncaughtException | TypeError $e) {
            $child->reject($e->getReason());
            throw $e;
        } catch (\Throwable $e) {
            $child->reject($e);
        }
    }

    /**
     * This method will make a pending promise settled. May be called immediately or deferred.
     *
     * @param  PromiseState  $state
     * @param  mixed         $value
     *
     * @return  void
     *
     * @throws Throwable
     */
    private function settle(PromiseState $state, mixed $value): void
    {
        if ($this->isSettled()) {
            return;
        }

        if ($state->isPending()) {
            throw new TypeError('Cannot settle as pending state.');
        }

        $this->state = $state;
        $this->value = $value;

        // Make scheduler done.
        // Scheduler may wait this promise before or after we do it.
        // If before done, the scheduler should sleep and block until done.
        // If after done, it should stop waiting immediately.
        $this->scheduleDone();

        if (
            // If now is not Promise constructing. Maybe we call resolve() deferred.
            // We should throw or log reject reasons.
            !$this->constructingSynchronously
            // If no children, handle rejection instantly.
            && $this->children === []
            && $state->isRejected()
        ) {
            $this->logOrThrow(new UncaughtException($value));

            return;
        }

        // If now is Promise constructing and synchronous settled
        // We should not throw Error. Just make self rejected and handle all children.
        foreach ($this->children as $child) {
            [$childPromise, $onFulfilled, $onRejected] = $child;

            $this->settleChild(
                $childPromise,
                $onFulfilled,
                $onRejected
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function getState(): PromiseState
    {
        return $this->state;
    }

    /**
     * resolved
     *
     * @param  mixed  $value
     *
     * @return  static
     *
     * @throws Throwable
     * @since  __DEPLOY_VERSION__
     */
    public static function resolved(mixed $value = null): static
    {
        return new static(
            function ($resolve) use ($value) {
                $resolve($value);
            }
        );
    }

    /**
     * rejected
     *
     * @param  mixed  $value
     *
     * @return  static
     *
     * @throws Throwable
     */
    public static function rejected(mixed $value = null): static
    {
        return new static(
            function ($resolve, $reject) use ($value) {
                $reject($value);
            }
        );
    }

    /**
     * @inheritDoc
     * @throws Throwable
     */
    public function resolve(mixed $value): void
    {
        static::resolvePromise($this, $value);
    }

    /**
     * @param  PromiseInterface  $promise
     * @param  mixed             $value
     *
     * @return void
     * @throws Throwable
     */
    private static function resolvePromise(PromiseInterface $promise, mixed $value): void
    {
        if ($value === $promise) {
            $promise->reject(new TypeError('Unable to resolve self.'));

            return;
        }

        // Case1: Do not do anything if is settled
        // @sync
        if ($promise->getState()->isSettled()) {
            return;
        }

        // Case2: If value is a settled self promise class, just sync it.
        // @sync
        if ($value instanceof self && !$value->isPending()) {
            $promise->settle($value->getState(), $value->value);
            return;
        }

        // Case3: If is a pending self class or a promise from other library with any state,
        // we call then() to sync from it. Note this flow will be asynchronous.
        // @async
        if ($value instanceof self || is_thenable($value)) {
            $value->then(
                [$promise, 'resolve'],
                [$promise, 'reject']
            );

            return;
        }

        // Case4: Otherwise we make self settled and fulfilled
        // @sync
        $promise->settle(PromiseState::FULFILLED, $value);
    }

    /**
     * @inheritDoc
     */
    public function reject(mixed $reason): void
    {
        if ($reason === $this) {
            $this->reject(new TypeError('Unable to resolve self.'));

            return;
        }

        if ($this->getState() !== PromiseState::PENDING) {
            return;
        }

        $this->settle(PromiseState::REJECTED, $reason);
    }

    /**
     * @inheritDoc
     *
     * @throws Throwable
     */
    public function wait(): mixed
    {
        if ($this->getState() === PromiseState::PENDING) {
            $this->scheduleWait();

            if ($this->getState() === PromiseState::PENDING) {
                throw new UnsettledException('Error, this promise has not settled.');
            }
        }

        if ($this->getState() === PromiseState::REJECTED) {
            if ($this->value instanceof Throwable) {
                $this->logOrThrow(new UncaughtException($this->value, $this->value));
            }

            $this->logOrThrow(new UncaughtException($this->value));
        }

        return $this->value;
    }

    /**
     * Log the uncaught reject reason.
     *
     * @param  UncaughtException  $e
     *
     * @return  void
     * @throws UncaughtException
     */
    private function logOrThrow(UncaughtException $e): void
    {
        $logger = $this->uncaughtLogger ?? static::$defaultUncaughtLogger;

        if ($logger) {
            $logger($e);
        } else {
            throw $e;
        }
    }

    /**
     * @param  callable  $callback
     * @param  Promise   $promise
     *
     * @return  void
     */
    protected function scheduleFor(callable $callback, self $promise): void
    {
        if ($promise->scheduleCursor->isScheduled()) {
            throw new \LogicException('A promise should not schedule more than once.');
        }

        ScheduleRunner::getInstance()->schedule($promise->scheduleCursor, $callback);

        $promise->scheduleCursor->setScheduled(true);
    }

    /**
     * waitAsync
     *
     * @return  void
     */
    protected function scheduleWait(): void
    {
        ScheduleRunner::getInstance()->wait($this->scheduleCursor);
    }

    /**
     * doneAsync
     *
     * @return  void
     */
    protected function scheduleDone(): void
    {
        $scheduleRunner = ScheduleRunner::getInstance();

        $scheduleRunner->done($this->scheduleCursor);
    }

    public function isSettled(): bool
    {
        return $this->getState()->isSettled();
    }

    public function isPending(): bool
    {
        return $this->getState()->isPending();
    }

    public function isFulfilled(): bool
    {
        return $this->getState()->isFulfilled();
    }

    public function isRejected(): bool
    {
        return $this->getState()->isRejected();
    }

    /**
     * Call construction callback.
     *
     * @param  callable  $cb
     *
     * @return  void
     * @throws Throwable
     * @throws \ReflectionException
     */
    private function constructing(#[\SensitiveParameter] callable $cb): void
    {
        $callback = Closure::fromCallable($cb);
        $ref = new ReflectionFunction($callback);

        $args = $ref->getNumberOfParameters();

        try {
            if ($args === 0) {
                $callback();
            } else {
                $callback(
                    // This is resolve() function in promise constructor.
                    // May be call instantly or deferred.
                    function ($value = null) {
                        $this->resolve($value);
                    },
                    // This is reject() function in promise constructor.
                    // May be call instantly or deferred.
                    function ($reason = null) {
                        $this->reject($reason);
                    }
                );
            }
        } catch (Throwable $e) {
            $this->resolve($e);
        }
    }
}
