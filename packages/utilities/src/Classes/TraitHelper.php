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
    /**
     * @link  http://php.net/manual/en/function.class-uses.php#110752
     *
     * @param  string|object  $class
     * @param  bool           $autoload
     *
     * @return  array
     */
    public static function classUsesRecursive(string|object $class, bool $autoload = true): array
    {
        $traits = [];

        do {
            $traits = [...$traits, ...(class_uses($class, $autoload) ?: [])];
        } while ($class = get_parent_class($class));

        return array_unique($traits);
    }

    public static function uses(string|object $class, string $trait, bool $autoload = true): bool
    {
        return in_array($trait, static::classUsesRecursive($class, $autoload), true);
    }
}
