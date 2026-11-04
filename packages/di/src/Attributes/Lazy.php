<?php

declare(strict_types=1);

namespace Windwalker\DI\Attributes;

use Attribute;

/**
 * Create the target as a PHP 8.4 lazy proxy: the real object is created on first property access,
 * so injecting it does not instantiate it until it is really used.
 *
 * Applies to a class, a constructor/method parameter or a property.
 *
 * Lazy is silently skipped (normal eager flow) when the class does not exist, is not instantiable
 * (interface, abstract class, trait, enum), or is/extends an internal class (e.g. ArrayObject, SplStack).
 * Any class-level attribute other than Lazy also disables proxy creation, even when ignored or excluded,
 * because attributes may replace the instance with an incompatible type.
 * A nullable parameter is only lazy if the container has the service registered.
 *
 * If the first access throws a DefinitionResolveException "Unable to initialize lazy object", the resolved real
 * instance is not compatible with the declared type: PHP requires the same class, or a proxy sub-class that adds
 * no properties and overrides neither __destruct nor __clone. Typically a service is bound to a sub-class with
 * extra properties. Type-hint the concrete class or remove #[Lazy] from it.
 *
 * Errors in constructors or dependencies are deferred to first access and wrapped in a
 * DefinitionResolveException "Error when lazy creating object" with the original exception as previous.
 *
 * @see \Windwalker\DI\DIOptions::$lazy
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class Lazy implements ContainerAttributeInterface
{
    public function __invoke(AttributeHandler $handler): callable
    {
        $container = $handler->container;

        // Property
        return static function () use ($container, $handler) {
            $bak = $container->options->lazy;
            $container->options->lazy = true;

            $value = $handler();

            $container->options->lazy = $bak;

            return $value;
        };
    }
}
