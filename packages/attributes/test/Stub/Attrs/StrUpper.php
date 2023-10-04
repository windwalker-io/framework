<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Attributes\Test\Stub\Attrs;

use Attribute;
use Closure;
use Windwalker\Attributes\AttributeHandler;
use Windwalker\Scalars\StringObject;

/**
 * The StrUpper class.
 */
#[Attribute]
class StrUpper
{
    public function __invoke(AttributeHandler $handler): Closure
    {
        return function (...$args) use ($handler) {
            $value = $handler(...$args);

            if ($value instanceof StringObject) {
                return $value->toUpperCase();
            }

            return strtoupper((string) $value);
        };
    }
}
