<?php

declare(strict_types=1);

namespace Windwalker\DI\Test\Injection\Attrs;

use Attribute;
use Windwalker\DI\Attributes\AttributeHandler;
use Windwalker\DI\Attributes\ContainerAttributeInterface;

/**
 * The ParamLower class.
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class ParamLower implements ContainerAttributeInterface
{
    public function __invoke(AttributeHandler $handler): callable
    {
        return fn(...$args) => $handler(...$args)->toLowerCase();
    }
}
