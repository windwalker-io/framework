<?php

declare(strict_types=1);

namespace Windwalker\Promise;

/**
 * Interface ExtendedPromiseInterface
 */
interface ExtendedPromiseInterface extends PromiseInterface
{
    /**
     * @param  callable|null  $onFulfilled
     *
     * @return static
     */
    public function done(?callable $onFulfilled = null): static;

    /**
     * @param  callable|null  $onRejected
     *
     * @return static
     */
    public function catch(?callable $onRejected): static;

    /**
     * @param  callable|null  $onFulfilledOrRejected
     *
     * @return static
     */
    public function finally(?callable $onFulfilledOrRejected): static;
}
