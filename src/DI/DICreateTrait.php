<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2020 .
 * @license    __LICENSE__
 */

namespace Windwalker\DI;

/**
 * DICreateTrait
 *
 * @since  {DEPLOY_VERSION}
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
     * @since  __DEPLOY_VERSION__
     */
    public static function di(...$args): ClassMeta
    {
        return Container::meta(
            static::class,
            $args
        );
    }
}
