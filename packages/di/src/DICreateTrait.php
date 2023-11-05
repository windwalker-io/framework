<?php

declare(strict_types=1);

namespace Windwalker\DI;

/**
 * Trait DICreateTrait
 */
trait DICreateTrait
{
    public static function di(...$args): Definition\ObjectBuilderDefinition
    {
        return create(
            static::class,
            ...$args
        );
    }
}
