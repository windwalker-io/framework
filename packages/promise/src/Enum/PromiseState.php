<?php

/**
 * Part of framework project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Promise\Enum;

/**
 * The PromiseStatus class.
 */
enum PromiseState
{
    case PENDING;

    case FULFILLED;

    case REJECTED;

    public function isSettled(): bool
    {
        return $this === self::FULFILLED || $this === self::REJECTED;
    }

    public function isFulfilled(): bool
    {
        return $this === self::FULFILLED;
    }

    public function isRejected(): bool
    {
        return $this === self::REJECTED;
    }

    public function isPending(): bool
    {
        return $this === self::PENDING;
    }
}
