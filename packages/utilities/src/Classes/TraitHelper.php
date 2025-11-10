<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Classes;

/**
 * The TraitHelper class.
 *
 * @since  3.0
 */
class TraitHelper
{
    public static array $cache = [];

    /**
     * @param  string|object  $class
     * @param  bool           $autoload
     *
     * @return  array
     */
    public static function classUsesRecursive(string|object $class, bool $autoload = true): array
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        if (isset(static::$cache[$class])) {
            return static::$cache[$class];
        }

        $traits = [];

        $current = $class;

        while ($current) {
            $traits = [...$traits, ...(class_uses($current, $autoload) ?: [])];
            $current = get_parent_class($current);
        }

        $searchQueue = $traits;

        while ($searchQueue) {
            $trait = array_pop($searchQueue);
            $usedByTrait = class_uses($trait, $autoload);

            if ($usedByTrait) {
                foreach ($usedByTrait as $t) {
                    if (!in_array($t, $traits, true)) {
                        $traits[] = $t;
                        $searchQueue[] = $t;
                    }
                }
            }
        }

        return static::$cache[$class] = array_values(array_unique($traits));
    }

    public static function uses(string|object $class, string $trait, bool $autoload = true): bool
    {
        return in_array($trait, static::classUsesRecursive($class, $autoload), true);
    }
}
