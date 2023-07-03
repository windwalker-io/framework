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
}
