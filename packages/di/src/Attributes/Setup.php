<?php

/**
 * Part of starter project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

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
        $ref = $handler->getReflector();
        $instance = $handler->getObject();

        if ($ref instanceof ReflectionMethod && $instance) {
            $ref->invoke($instance);
        }

        return $handler->get();
    }
}
