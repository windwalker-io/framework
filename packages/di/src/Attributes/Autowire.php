<?php

declare(strict_types=1);

namespace Windwalker\DI\Attributes;

use Attribute;
use ReflectionParameter;
use Windwalker\DI\Container;
use Windwalker\DI\DIOptions;

/**
 * The Autowire class.
 */
#[Attribute]
class Autowire implements ContainerAttributeInterface
{
    public function __invoke(AttributeHandler $handler): callable
    {
        $container = $handler->container;
        $reflector = $handler->reflector;

        if ($reflector instanceof ReflectionParameter && $reflector->getType()) {
            return function &(...$args) use ($handler, $reflector, $container) {
                $value = $handler(...$args);

                // Only value is NULL needs autowire.
                if ($value !== null) {
                    return $value;
                }

                $resolver = $container->getDependencyResolver();

                // resolveParameterValue() will be called in resolveParameterDependency()
                $value = &$resolver->resolveParameterDependency($reflector, [], new DIOptions(autowire: true));

                return $value;
            };
        }

        return static function ($args, DIOptions $options) use ($handler) {
            $options->autowire = true;

            return $handler($args, $options);
        };
    }
}
