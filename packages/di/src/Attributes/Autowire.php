<?php

declare(strict_types=1);

namespace Windwalker\DI\Attributes;

use Attribute;
use ReflectionParameter;
use Windwalker\DI\Container;

/**
 * The Autowire class.
 */
#[Attribute]
class Autowire implements ContainerAttributeInterface
{
    public function __invoke(AttributeHandler $handler): callable
    {
        $container = $handler->getContainer();
        $reflector = $handler->getReflector();

        if ($reflector instanceof ReflectionParameter && $reflector->getType()) {
            return function (...$args) use ($handler, $reflector, $container) {
                $value = $handler(...$args);

                // Only value is NULL needs autowire.
                if ($value !== null) {
                    return $value;
                }

                $resolver = $container->getDependencyResolver();

                return $resolver->resolveParameterValue(
                    $resolver->resolveParameterDependency($reflector, [], Container::AUTO_WIRE),
                );
            };
        }

        return static function ($args, $options) use ($handler) {
            $options |= Container::AUTO_WIRE;

            return $handler($args, $options);
        };
    }
}
