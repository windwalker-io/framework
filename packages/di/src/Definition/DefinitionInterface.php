<?php

declare(strict_types=1);

namespace Windwalker\DI\Definition;

use Windwalker\DI\Container;

/**
 * Interface DefinitionInterface
 */
interface DefinitionInterface
{
    /**
     * Resolve this definition.
     *
     * @param  Container              $container  The Container object.
     * @param  array                  $args
     * @param  \UnitEnum|string|null  $tag
     *
     * @return mixed
     */
    public function resolve(Container $container, array $args = [], \UnitEnum|string|null $tag = null): mixed;

    /**
     * Set new value or factory callback to this definition.
     *
     * @param  mixed  $value  Value or callable.
     *
     * @return  void
     */
    public function set(mixed $value): void;
}
