<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\DI\Definition;

use Closure;
use InvalidArgumentException;

/**
 * The DefinitionFactory class.
 */
class DefinitionFactory
{
    public static function create(mixed $value, int $options = 0): StoreDefinitionInterface
    {
        return new StoreDefinition('', $value, $options);
    }

    public static function wrap(mixed $value): StoreDefinitionInterface
    {
        if ($value instanceof StoreDefinitionInterface) {
            return $value;
        }

        return new StoreDefinition('', $value, 0);
    }

    public static function isSameClass(mixed $a, mixed $b): bool
    {
        $class1 = static::getClassName($a);
        $class2 = static::getClassName($b);

        return strtolower(trim($class1, '\\')) === strtolower(trim($class2, '\\'));
    }

    public static function getClassName(mixed $obj): string
    {
        if ($obj instanceof ObjectBuilderDefinition) {
            $obj = $obj->getClass();
        }

        if ($obj instanceof Closure) {
            return spl_object_hash($obj);
        }

        if (is_object($obj)) {
            return get_class($obj);
        }

        if (is_string($obj) || is_stringable($obj)) {
            return $obj;
        }

        throw new InvalidArgumentException('Invalid object type, should be object or class name.');
    }
}
