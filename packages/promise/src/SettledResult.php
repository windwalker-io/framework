<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Promise;

use Windwalker\Promise\Enum\PromiseState;

/**
 * The SettledResult class.
 */
readonly class SettledResult
{
    public function __construct(
        public PromiseState $status,
        public mixed $value
    ) {
        //
    }

    public static function fulfilled(mixed $value): static
    {
        return new static(PromiseState::FULFILLED, $value);
    }

    public static function rejected(mixed $value): static
    {
        return new static(PromiseState::REJECTED, $value);
    }
}
