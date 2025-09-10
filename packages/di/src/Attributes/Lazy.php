<?php

declare(strict_types=1);

namespace Windwalker\DI\Attributes;

use Windwalker\DI\DIOptions;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_PARAMETER | \Attribute::TARGET_PROPERTY)]
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
