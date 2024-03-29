<?php

declare(strict_types=1);

namespace Windwalker\Promise\Helper;

use Windwalker\Promise\ExtendedPromiseInterface;

/**
 * This is just a helper interface to make auto-completion works.
 */
interface ReturnPromiseInterface
{
    /**
     * __invoke
     *
     * @param  mixed  ...$args
     *
     * @return  ExtendedPromiseInterface
     */
    public function __invoke(...$args): ExtendedPromiseInterface;
}
