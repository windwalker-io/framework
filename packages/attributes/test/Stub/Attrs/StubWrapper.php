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

/**
 * The StubWrapper class.
 */
#[Attribute]
class StubWrapper
{
    public object $instance;

    public array $options;

    public function __invoke(AttributeHandler $handler): Closure
    {
        $this->options = $handler->getOptions();

        return function (...$args) use ($handler) {
            $this->instance = $handler(...$args);

            return $this;
        };
    }
}
