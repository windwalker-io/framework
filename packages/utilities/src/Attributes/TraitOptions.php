<?php

/**
 * Part of unicorn project.
 *
 * @copyright  Copyright (C) 2021 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Utilities\Attributes;

use Windwalker\Attributes\AttributesAccessor;
use Windwalker\Utilities\Cache\RuntimeCacheTrait;

/**
 * The TraitOptions class.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class TraitOptions
{
    use RuntimeCacheTrait;

    public function __construct(
        public array $options = []
    ) {
    }

    public static function getOption(object|string $class, string $traitName, string $name): mixed
    {
        return static::getOptions($class, $traitName)[$name] ?? null;
    }

    public static function getOptions(object|string $class, string $traitName): array
    {
        $allOptions = static::getAllOptions($class);

        return $allOptions[$traitName] ?? [];
    }

    public static function getAllOptions(object|string $class): array
    {
        if (is_object($class)) {
            $class = $class::class;
        }

        return static::$cacheStorage[$class] ??= static::findAttribute($class)?->options ?? [];
    }

    public static function findAttribute(string $class): ?static
    {
        return AttributesAccessor::getFirstAttributeInstance($class, static::class);
    }
}
