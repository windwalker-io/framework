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
     * @var static[]
     */
    protected array $handlers = [];

    /**
     * @var ScheduleCursor|null
     */
    protected ?ScheduleCursor $scheduleCursor = null;

    public ?\Closure $uncaughtLogger = null;

    public static ?\Closure $defaultUncaughtLogger = null;

    protected bool $initialising = false;

    /**
     * create
     *
     * @param  callable|null  $resolver
     *
     * @return static
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
     */
    public function __construct(#[\SensitiveParameter] ?callable $resolver = null)
    {
        $resolver = $resolver ?: static function () {
            //
        };

        $this->initialising = true;

        $this->call($resolver);

        $this->initialising = false;
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
        $onFulfilled = is_callable($onFulfilled)
            ? $onFulfilled
            : nope();

        // if ($this->getState() === PromiseState::PENDING) {
        //     $child = new static();
        //
        //     $this->handlers[] = [
        //         $child,
        //         $onFulfilled,
        //         $onRejected,
        //     ];
        //
        //     return $child;
        // }

        return $this->handlers[] = $this->chainNewPromise($onFulfilled, $onRejected);
    }

    private function chainNewPromise(callable $onFulfilled, ?callable $onRejected): static
    {
        return $this->chainPromise(static::create(), $onFulfilled, $onRejected);
    }

    private function chainPromise(self $promise, callable $onFulfilled, ?callable $onRejected): self
    {
        $this->scheduleFor(
            function () use ($onRejected, $onFulfilled, $promise) {
                try {
                    if ($this->getState() === PromiseState::FULFILLED) {
                        $promise->innerResolve($onFulfilled($this->value));
                    } elseif ($onRejected) {
                        $promise->innerResolve($onRejected($this->value));
                    } else {
                        $promise->innerReject($this->value);
                    }
                } catch (UncaughtException $e) {
                    $promise->innerReject($e->getReason());
                    throw $e;
                } catch (\Throwable $e) {
                    $promise->innerReject($e);
                }
            },
            $promise
        );

        return $promise;
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
     * @since  __DEPLOY_VERSION__
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
        if ($this->innerResolve($value)) {
            $this->scheduleWait();
        }
    }

    private function innerResolve(mixed $value): bool
    {
        return static::resolvePromise($this, $value);
    }

    /**
     * @inheritDoc
     */
    public function reject(mixed $reason): void
    {
        if ($this->innerReject($reason)) {
            $this->scheduleWait();
        }
    }

    private function innerReject(mixed $reason): bool
    {
        if ($reason === $this) {
            $this->reject(new TypeError('Unable to resolve self.'));

            return false;
        }

        if ($this->getState() !== PromiseState::PENDING) {
            return false;
        }

        $this->settle(PromiseState::REJECTED, $reason);

        return true;
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
     * @param  PromiseInterface  $promise
     * @param  mixed             $value
     *
     * @return  bool
     * @throws Throwable
     */
    private static function resolvePromise(PromiseInterface $promise, mixed $value): bool
    {
        if ($value === $promise) {
            $promise->reject(new TypeError('Unable to resolve self.'));

            return false;
        }

        if ($promise->getState() !== PromiseState::PENDING) {
            return false;
        }

        if ($value instanceof self) {
            $promise->settle($value->getState(), $value->value);
            return true;
        }

        if (is_thenable($value)) {
            $value->then(
                [$promise, 'resolve'],
                [$promise, 'reject']
            );

            return true;
        }

        $promise->settle(PromiseState::FULFILLED, $value);

        return true;
    }

    /**
     * @param  callable  $callback
     * @param  Promise   $promise
     *
     * @return  void
     */
    protected function scheduleFor(callable $callback, self $promise): void
    {
        if ($promise->scheduleCursor) {
            throw new \LogicException('A promise should not schedule again.');
        }

        $promise->scheduleCursor = ScheduleRunner::getInstance()->schedule($callback);
    }

    /**
     * waitAsync
     *
     * @return  void
     */
    protected function scheduleWait(): void
    {
        if (!$this->scheduleCursor) {
            $this->scheduleFor(
                // We must done schedule instantly, otherwise the waiting will be forever.
                function () {
                    $this->scheduleDone();
                },
                $this
            );
        }

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

        // Free cursor
        if ($this->scheduleCursor) {
            $scheduleRunner->release($this->scheduleCursor);

            $this->scheduleCursor = null;
        }
    }

    /**
     * settle
     *
     * @param  PromiseState  $state
     * @param  mixed         $value
     *
     * @return  void
     *
     * @throws Throwable
     * @since  __DEPLOY_VERSION__
     */
    private function settle(PromiseState $state, mixed $value): void
    {
        $this->state = $state;
        $this->value = $value;

        $this->scheduleDone();

        if ($this->handlers === [] && $state === PromiseState::REJECTED && !$this->initialising) {
            $this->logOrThrow(new UncaughtException($value));

            return;
        }

        foreach ($this->handlers as $handler) {
            $promise = $handler;

            $promise->settle($this->getState(), $this->value);
        }
    }

    /**
     * Calling callback.
     *
     * This method is a clone of Reactphp/Promise
     *
     * @see https://github.com/reactphp/promise
     *
     * @param  callable  $cb
     *
     * @return  void
     * @throws Throwable
     */
    private function call(#[\SensitiveParameter] callable $cb): void
    {
        $callback = Closure::fromCallable($cb);
        $ref = new ReflectionFunction($callback);

        $args = $ref->getNumberOfParameters();

        try {
            if ($args === 0) {
                $callback();
            } else {
                $callback(
                    function ($value = null) {
                        $this->innerResolve($value);
                    },
                    function ($reason = null) {
                        $this->innerReject($reason);
                    }
                );
            }
        } catch (UncaughtException $e) {
            throw $e;
        } catch (Throwable $e) {
            $this->innerResolve($e);
        }
    }
}
