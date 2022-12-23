<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2022 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Enum;

use MyCLabs\Enum\Enum;

/**
 * The EnumSingleton class.
 */
class EnumSingleton extends Enum
{
    /**
     * @inheritDoc
     */
    public static function __callStatic($name, $arguments)
    {
        return self::$instances[static::class][$name] ??= parent::__callStatic($name, $arguments);
    }
}