<?php

declare(strict_types=1);

namespace Windwalker\DI\Attributes;

use Attribute;
use ReflectionMethod;

/**
 * To boot class when created from Container.
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Setup implements ContainerAttributeInterface
{
    public function __invoke(AttributeHandler $handler): callable
    {
        $ref = $handler->reflector;
        $instance = $handler->object;

        if ($ref instanceof ReflectionMethod && $instance) {
            $ref->invoke($instance);
        }

        return $handler->get();
    }
}
