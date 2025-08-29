<?php

declare(strict_types=1);

namespace Windwalker\DI;

use Windwalker\Utilities\Options\RecordOptionsTrait;

class MergeOptions
{
    use RecordOptionsTrait {
        wrap as parentWrap;
    }

    public function __construct(
        public bool $recursive = false,
        public bool $override = false,
    ) {
    }

    public static function wrap(mixed $values): static
    {
        if (is_int($values)) {
            return new static(
                recursive: (bool) ($values & Container::MERGE_RECURSIVE),
                override: (bool) ($values & Container::MERGE_OVERRIDE),
            );
        }

        return static::parentWrap($values);
    }
}
