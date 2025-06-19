<?php

declare(strict_types=1);

namespace Windwalker\Promise;

use LogicException;
use Windwalker\Promise\Enum\PromiseState;

/**
 * Interface PromiseInterface
 */
interface PromiseInterface
{
    /**
     * @deprecated  Use PromiseState enum instead.
     */
    public const PromiseState PENDING = PromiseState::PENDING;

    /**
     * @deprecated  Use PromiseState enum instead.
     */
    public const PromiseState FULFILLED = PromiseState::FULFILLED;

    /**
     * @deprecated  Use PromiseState enum instead.
     */
    public const PromiseState REJECTED = PromiseState::REJECTED;

    /**
     * Appends fulfillment and rejection handlers to the promise, and returns
     * a new promise resolving to the return value of the called handler.
     *
     * @see https://promisesaplus.com/#the-then-method
     *
     * @param  callable|mixed  $onFulfilled  Invoked when the promise fulfills.
     * @param  callable|mixed  $onRejected   Invoked when the promise is rejected.
     *
     * @return static
     */
    public function then(
        ?callable $onFulfilled = null,
        ?callable $onRejected = null
    ): static;

    /**
     * Get the state of the promise ("pending", "rejected", or "fulfilled").
     *
     * The three states can be checked against the constants defined on
     * PromiseState: PENDING, FULFILLED, and REJECTED.
     *
     * @return PromiseState
     */
    public function getState(): PromiseState;

    /**
     * Waits until the promise completes if possible.
     *
     * If the promise cannot be waited on, then the promise will be rejected.
     *
     * @return mixed
     * @throws LogicException if the promise has no wait function or if the
     *                         promise does not settle after waiting.
     */
    public function wait(): mixed;
}
