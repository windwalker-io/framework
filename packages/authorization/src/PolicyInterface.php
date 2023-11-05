<?php

declare(strict_types=1);

namespace Windwalker\Authorization;

/**
 * The PolicyInterface class.
 *
 * @since  3.0
 */
interface PolicyInterface
{
    /**
     * authorise
     *
     * @param  mixed  $user
     * @param  mixed  ...$args
     *
     * @return  boolean
     */
    public function authorize(mixed $user, mixed ...$args): bool;
}
