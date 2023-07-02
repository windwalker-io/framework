<?php

/**
 * Part of framework project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Promise;

use Windwalker\Promise\Enum\PromiseStatus;

/**
 * The SettledResult class.
 */
readonly class SettledResult
{
    public function __construct(
        public PromiseStatus $status,
        public mixed $value
    ) {
        //
    }

    public static function fulfilled(mixed $value): static
    {
        return new static(PromiseStatus::FULFILLED, $value);
    }

    public static function rejected(mixed $value): static
    {
        return new static(PromiseStatus::REJECTED, $value);
    }
}
