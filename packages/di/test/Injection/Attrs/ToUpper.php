<?php

declare(strict_types=1);

namespace Windwalker\DI\Test\Injection\Attrs;

use Attribute;
use Windwalker\DI\Attributes\AttributeHandler;
use Windwalker\DI\Attributes\ContainerAttributeInterface;

/**
 * The ToUpper class.
 */
#[Attribute]
class ToUpper implements ContainerAttributeInterface
{
    public function __invoke(AttributeHandler $handler): callable
    {
        return fn(...$args) => strtoupper($handler(...$args));
    }
}
