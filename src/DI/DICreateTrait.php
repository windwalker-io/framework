<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2020 .
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\DI;

/**
 * DICreateTrait
 *
 * @since  3.5.21
 */
trait DICreateTrait
{
    /**
     * di
     *
     * @param mixed ...$args
     *
     * @return  ClassMeta
     *
     * @since  3.5.21
     */
    public static function di(...$args): ClassMeta
    {
        return Container::meta(
            static::class,
            $args
        );
    }
}
