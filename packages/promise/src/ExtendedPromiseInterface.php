<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Promise;

/**
 * Interface ExtendedPromiseInterface
 *
 * @since  __DEPLOY_VERSION__
 */
interface ExtendedPromiseInterface extends PromiseInterface
{
    /**
     * @param  callable|null  $onFulfilled
     *
     * @return static
     */
    public function done(?callable $onFulfilled = null);

    /**
     * @param  callable  $onRejected
     *
     * @return static
     */
    public function catch(?callable $onRejected);

    /**
     * @param  callable  $onFulfilledOrRejected
     *
     * @return static
     */
    public function finally(?callable $onFulfilledOrRejected);
}
