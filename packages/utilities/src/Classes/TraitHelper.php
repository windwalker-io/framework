<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

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
     * classUsesRecursive
     *
     * @link  http://php.net/manual/en/function.class-uses.php#110752
     *
     * @param  string|object  $class
     * @param  bool           $autoload
     *
     * @return  array
     */
    public static function classUsesRecursive(string $class, bool $autoload = true): array
    {
        $traits = [];

        do {
            $traits[] = class_uses($class, $autoload);
        } while ($class = get_parent_class($class));

        foreach ($traits as $trait => $same) {
            $traits[] = class_uses($trait, $autoload);
        }

        return array_unique(array_merge(...$traits));
    }
}
