<?php

declare(strict_types=1);

namespace Windwalker\Utilities;

use BadMethodCallException;
use Closure;
use InvalidArgumentException;
use JetBrains\PhpStorm\Pure;
use stdClass;
use Traversable;
use Windwalker\Utilities\Assert\Assert;
use Windwalker\Utilities\Assert\TypeAssert;
use Windwalker\Utilities\Classes\PreventInitialTrait;
use Windwalker\Utilities\Contract\DumpableInterface;
use Windwalker\Utilities\Exception\CastingException;
use Windwalker\Utilities\Exception\ExceptionFactory;

/**
 * The TypeCast class.
 *
 * @method static int|null tryInteger($value, bool $strict = false)
 * @method static float|null tryFloat($value, bool $strict = false)
 * @method static int|float|null tryNumeric($value, bool $strict = false)
 * @method static string|null tryString($value, bool $strict = false)
 * @method static bool|null tryBoolean($value, bool $strict = false)
 * @method static array|null tryArray($value, bool $strict = false)
 * @method static object|null tryObject($value, bool $strict = false)
 * @method static int|null safeInteger($value)
 * @method static float|null safeFloat($value)
 * @method static int|float|null safeNumeric($value)
 * @method static string|null safeString($value)
 * @method static bool|null safeBoolean($value)
 * @method static array|null safeArray($value)
 * @method static object|null safeObject($value)
 * @method static int mustInteger($value)
 * @method static float mustFloat($value)
 * @method static int|float mustNumeric($value)
 * @method static string mustString($value)
 * @method static bool mustBoolean($value)
 * @method static array mustArray($value)
 * @method static object mustObject($value)
 *
 * @since  4.0
 */
abstract class TypeCast
{
    use PreventInitialTrait;

    public const TYPE_INT = 'int';

    public const TYPE_FLOAT = 'float';

    public const TYPE_STRING = 'string';

    public const TYPE_BOOL = 'bool';

    public const TYPE_ARRAY = 'array';

    public const TYPE_OBJECT = 'object';

    public static function extractEnum(\UnitEnum $data): string|int
    {
        if ($data instanceof \BackedEnum) {
            return $data->value;
        }

        return $data->name;
    }

    /**
     * Utility function to convert all types to an array.
     *
     * @param  mixed  $data          The data to convert.
     * @param  bool   $recursive     Recursive if data is nested.
     * @param  bool   $onlyDumpable  Objects only implements DumpableInterface will convert to array.
     *
     * @return  array  The converted array.
     */
    public static function toArray(mixed $data, bool $recursive = false, bool $onlyDumpable = false): array
    {
        // Ensure the input data is an array.
        if ($data instanceof DumpableInterface) {
            $data = $data->dump($recursive);

            if ($recursive) {
                return $data;
            }
        } elseif ($data instanceof Traversable) {
            $data = iterator_to_array($data);
        } elseif (is_object($data)) {
            $data = get_object_vars($data);
        } else {
            $data = (array) $data;
        }

        if ($recursive) {
            foreach ($data as $k => $value) {
                if (is_array($value)) {
                    $data[$k] = static::toArray($value, $recursive, $onlyDumpable);
                } elseif (is_object($value)) {
                    if ($onlyDumpable && $value instanceof DumpableInterface) {
                        $data[$k] = static::toArray($value, $recursive, $onlyDumpable);
                    } elseif (!$onlyDumpable) {
                        $data[$k] = static::toArray($value, $recursive, $onlyDumpable);
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @param  mixed  $iterable
     *
     * @return  iterable
     *
     * @since  3.5
     */
    #[Pure]
    public static function toIterable(
        mixed $iterable
    ): iterable {
        if (is_iterable($iterable)) {
            return $iterable;
        }

        if (is_object($iterable)) {
            return get_object_vars($iterable);
        }

        return (array) $iterable;
    }

    /**
     * Utility function to map an array to a stdClass object.
     *
     * @param  array   $array      The array to map.
     * @param  bool    $recursive  Recursive.
     * @param  string  $class      Name of the class to create
     *
     * @return  object  The object mapped from the given array
     *
     * @since   2.0
     */
    public static function toObject(array $array, bool $recursive = false, string $class = stdClass::class): object
    {
        $obj = new $class();

        foreach ($array as $k => $v) {
            if (is_array($v) && $recursive) {
                $obj->$k = static::toObject($v, $recursive, $class);
            } else {
                $obj->$k = $v;
            }
        }

        return $obj;
    }

    /**
     * Convert all to string.
     *
     * @param  mixed  $data  The data to convert.
     * @param  bool   $dump  If is array or object, will dump it if this argument set to TRUE.
     *
     * @return  string
     *
     * @since  3.5
     */
    public static function toString(mixed $data, bool $dump = false): string
    {
        if ($data instanceof \UnitEnum) {
            return (string) static::extractEnum($data);
        }

        if ($data instanceof Closure) {
            return static::toString($data());
        }

        if (is_stringable($data)) {
            return (string) $data;
        }

        if (is_array($data)) {
            $data = $dump ? Arr::dump($data) : 'Array()';
        }

        if (is_object($data)) {
            $data = $dump ? Arr::dump($data) : sprintf('[Object %s]', get_class($data));
        }

        return (string) $data;
    }

    /**
     * forceString
     *
     * @param  mixed  $data  The data to convert.
     *
     * @return  string
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function forceString(mixed $data): string
    {
        return static::toString($data, true);
    }

    /**
     * mapAs
     *
     * @param  array   $src
     * @param  string  $typeOrClass
     *
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function mapAs(array $src, string $typeOrClass): array
    {
        if (class_exists($typeOrClass)) {
            return array_map(
                static function ($value) use ($typeOrClass) {
                    return new $typeOrClass($value);
                },
                $src
            );
        }

        $typeOrClass = strtolower($typeOrClass);

        if ($typeOrClass === 'array') {
            return array_map(
                [static::class, 'toArray'],
                $src
            );
        }

        if ($typeOrClass === 'string') {
            return array_map(
                static function ($value) {
                    return (string) $value;
                },
                $src
            );
        }

        if ($typeOrClass === 'int' || $typeOrClass === 'integer') {
            return array_map(
                static function ($value) {
                    return (int) $value;
                },
                $src
            );
        }

        if ($typeOrClass === 'float' || $typeOrClass === 'double') {
            return array_map(
                static function ($value) {
                    return (float) $value;
                },
                $src
            );
        }

        if ($typeOrClass === 'bool' || $typeOrClass === 'boolean') {
            return array_map(
                static function ($value) {
                    return (bool) $value;
                },
                $src
            );
        }

        throw new InvalidArgumentException(sprintf('%s is not a valid type or class name.', $typeOrClass));
    }

    /**
     * Try convert to another type or return NULL if unable to cast.
     *
     * @see    https://wiki.php.net/rfc/safe_cast
     *
     * @param  mixed   $value
     * @param  string  $type
     * @param  bool    $strict
     *
     * @return  mixed
     */
    public static function try(mixed $value, string $type, bool $strict = false): mixed
    {
        if ($value instanceof \UnitEnum) {
            $value = static::extractEnum($value);
        }

        switch (strtolower($type)) {
            case 'int':
            case 'integer':
                if ($strict) {
                    return static::canSafeConvert($value, 'integer') ? (int) $value : null;
                }

                if (is_scalar($value)) {
                    return (int) $value;
                }

                return null;

            case 'float':
            case 'double':
            case 'real':
                if ($strict) {
                    return static::canSafeConvert($value, 'float') ? (float) $value : null;
                }

                if (is_scalar($value)) {
                    return (float) $value;
                }

                return null;

            case 'number':
            case 'numeric':
                if (static::canSafeConvert($value, 'integer')) {
                    return static::tryInteger($value);
                }

                if (static::canSafeConvert($value, 'float')) {
                    return static::tryFloat($value);
                }

                return $strict ? null : (int) $value;

            case 'string':
                if ($strict) {
                    return static::canSafeConvert($value, 'string') ? (string) $value : null;
                }

                return is_stringable($value) ? (string) $value : null;

            case 'bool':
            case 'boolean':
                if ($strict) {
                    return static::canSafeConvert($value, 'bool') ? (bool) $value : null;
                }

                return (bool) $value;

            case 'array':
                if ($strict) {
                    return static::canSafeConvert($value, 'array') ? (array) $value : null;
                }

                return (array) $value;

            case 'object':
                if ($strict) {
                    return static::canSafeConvert($value, 'object') ? $value : null;
                }

                return (object) $value;

            default:
                return null;
        }
    }

    public static function safe(mixed $value, string $type): mixed
    {
        return static::try($value, $type, true);
    }

    public static function must(mixed $value, string $type): mixed
    {
        $converted = static::try($value, $type, true);

        if ($converted === null) {
            throw new CastingException(
                sprintf(
                    'Safe convert value %s to type "%s" failed.',
                    TypeAssert::describeValue($value),
                    $type
                )
            );
        }

        return $converted;
    }

    /**
     * The safe cast method. This method is based on:
     * - PHP Safe Casting Functions RFC: https://wiki.php.net/rfc/safe_cast
     * - PolyCast: https://github.com/theodorejb/PolyCast
     *
     * @param  mixed   $value
     * @param  string  $type
     *
     * @return  bool
     */
    public static function canSafeConvert(mixed $value, string $type): bool
    {
        switch (strtolower($type)) {
            case 'int':
            case 'integer':
                // Fork from: https://github.com/theodorejb/PolyCast
                switch (gettype($value)) {
                    case 'integer':
                        return true;
                    case 'float':
                    case 'double':
                        return $value === (float) (int) $value;
                    case 'string':
                        $intString = (string) (int) $value;
                        $floatString = (string) $value;

                        if (str_contains($floatString, '.')) {
                            $floatString = rtrim(rtrim($floatString, '0'), '.');
                        }

                        if ($floatString !== $intString && (string) $value !== "+$intString") {
                            return false;
                        }

                        return $value <= PHP_INT_MAX && $value >= PHP_INT_MIN;
                    default:
                        return false;
                }

            case 'float':
            case 'double':
            case 'real':
            case 'number':
            case 'numeric':
                // Fork from: https://github.com/theodorejb/PolyCast
                switch (gettype($value)) {
                    case "double":
                    case "integer":
                        return true;
                    case "string":
                        // Reject leading zeros unless they are followed by a decimal point
                        if (strlen($value) > 1 && $value[0] === '0' && $value[1] !== '.') {
                            return false;
                        }

                        // Use regular expressions to check is valid float expression.
                        // Based on http://php.net/manual/en/language.types.float.php
                        $lnum    = "[0-9]+";
                        $dnum    = "([0-9]*[\.]{$lnum})|({$lnum}[\.][0-9]*)";
                        $expDnum = "/^[+-]?(({$lnum}|{$dnum})[eE][+-]?{$lnum})$/";

                        return
                            preg_match("/^[+-]?{$lnum}$/", $value) ||
                            preg_match("/^[+-]?{$dnum}$/", $value) ||
                            preg_match($expDnum, $value);
                    default:
                        return false;
                }

            case 'string':
                return match (gettype($value)) {
                    'string', 'integer', 'double' => true,
                    'object' => $value instanceof \Stringable,
                    default => false,
                };

            case 'bool':
            case 'boolean':
                return is_bool($value);

            case 'array':
                return is_array($value);

            case 'object':
                return is_object($value);

            default:
                return false;
        }
    }

    /**
     * @param  string  $name
     * @param  array   $args
     *
     * @return  mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function __callStatic(string $name, array $args): mixed
    {
        $tryType = match ($name) {
            'tryInteger' => static::TYPE_INT,
            'tryFloat' => static::TYPE_FLOAT,
            'tryNumeric' => 'numeric',
            'tryString' => static::TYPE_STRING,
            'tryBoolean' => static::TYPE_BOOL,
            'tryArray' => static::TYPE_ARRAY,
            'tryObject' => static::TYPE_OBJECT,
            default => null
        };

        if ($tryType) {
            return static::try($args[0], $tryType, $args[1] ?? false);
        }

        $safeType = match ($name) {
            'safeInteger' => static::TYPE_INT,
            'safeFloat' => static::TYPE_FLOAT,
            'safeNumeric' => 'numeric',
            'safeString' => static::TYPE_STRING,
            'safeBoolean' => static::TYPE_BOOL,
            'safeArray' => static::TYPE_ARRAY,
            'safeObject' => static::TYPE_OBJECT,
            default => null
        };

        if ($safeType) {
            return static::safe($args[0], $safeType);
        }

        $mustType = match ($name) {
            'mustInteger' => static::TYPE_INT,
            'mustFloat' => static::TYPE_FLOAT,
            'mustNumeric' => 'numeric',
            'mustString' => static::TYPE_STRING,
            'mustBoolean' => static::TYPE_BOOL,
            'mustArray' => static::TYPE_ARRAY,
            'mustObject' => static::TYPE_OBJECT,
            default => null
        };

        if ($mustType) {
            return static::must($args[0], $mustType);
        }

        throw ExceptionFactory::badMethodCall($name, static::class);
    }
}
