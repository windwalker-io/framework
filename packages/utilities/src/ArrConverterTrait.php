<?php

declare(strict_types=1);

namespace Windwalker\Utilities;

use Windwalker\Utilities\Assert\TypeAssert;

/**
 * The ArrConverter class.
 *
 * @since  3.5
 */
trait ArrConverterTrait
{
    public const GROUP_TYPE_ARRAY = 1 << 0;

    public const GROUP_TYPE_KEY_BY = 1 << 1;

    public const GROUP_TYPE_MIX = 1 << 2;

    public const SAME_KEY_OVERRIDE = 1 << 3;

    /**
     * Flip array key and value and make 2-dimensional array to 1-dimensional.
     *
     * Similar to array_flip, but it handles 2-dimensional array.
     *
     * @param  array  $array
     *
     * @return  array
     *
     * @since  3.5.11
     */
    public static function flipMatrix(array $array): array
    {
        $new = [];

        foreach ($array as $k => $v) {
            foreach ($v as $k2 => $v2) {
                $new[$v2] = $k;
            }
        }

        return $new;
    }

    /**
     * Transpose a two-dimensional matrix array.
     *
     * @param  array  $array  An array with two level.
     *
     * @return array An pivoted array.
     */
    public static function transpose(array $array): array
    {
        $new = [];
        $keys = array_keys($array);

        foreach ($keys as $k => $val) {
            foreach ((array) $array[$val] as $k2 => $v2) {
                $new[$k2][$val] = $v2;
            }
        }

        return $new;
    }

    /**
     * Pivot $origin['prefix_xxx'] to $result['prefix']['xxx'].
     *
     * @param  array|object  $origin        Origin array to pivot.
     * @param  string        $prefix        A prefix text.
     * @param  bool          $removeOrigin  Remove origin value.
     *
     * @return  array  Pivoted array.
     */
    public static function groupPrefix(array|object &$origin, string $prefix, bool $removeOrigin = false): array
    {
        $target = [];

        foreach ((array) $origin as $key => $row) {
            if (str_starts_with($key, $prefix)) {
                $key2 = mb_substr($key, mb_strlen($prefix));
                $target = Arr::set($target, $key2, $row);

                if ($removeOrigin) {
                    $origin = Arr::remove($origin, $key);
                }
            }
        }

        return $target;
    }

    /**
     * Pivot $origin['prefix']['xxx'] to $target['prefix_xxx'].
     *
     * @param  array|object       $origin  Origin array to pivot.
     * @param  string             $prefix  A prefix text.
     * @param  array|object|null  $target  A target array to store pivoted value.
     *
     * @return object|array Pivoted array.
     */
    public static function extractPrefix(
        array|object $origin,
        string $prefix,
        array|object|null $target = null
    ): object|array {
        $target = is_object($target) ? $target : (array) $target;

        foreach ((array) $origin as $key => $val) {
            $key = $prefix . $key;

            if (!Arr::has($target, $key)) {
                $target = Arr::set($target, $key, $val);
            }
        }

        return $target;
    }

    /**
     * Re-group an array to create a reverse lookup of an array of scalars, arrays or objects.
     *
     * @param  array            $array  The source array data.
     * @param  string|\Closure  $key    Where the elements of the source array are objects
     *                                  or arrays, the key to pivot on.
     * @param  int              $flags  The group flags.
     *
     * @return array An array of arrays grouped either on the value of the keys,
     *               or an individual key of an object or array.
     *
     * @since  2.0
     */
    public static function group(array $array, string|\Closure|null $key, int $flags = self::GROUP_TYPE_ARRAY): array
    {
        if (is_string($key) || $key === null) {
            return static::groupByPath($array, $key, $flags, '');
        }

        return static::groupByCallback($array, $key, $flags);
    }

    public static function groupByPath(
        array $array,
        ?string $key = null,
        int $flags = self::GROUP_TYPE_ARRAY,
        string $delimiter = '.'
    ): array {
        return static::groupByCallback(
            $array,
            static function ($value, $i) use ($delimiter, $key) {
                if (is_array($value) || is_object($value)) {
                    if (!Arr::has($value, $key, $delimiter)) {
                        yield null => null;
                    }

                    yield Arr::get($value, $key, $delimiter) => $value;
                }

                yield $value => $i;
            },
            $flags
        );
    }

    private static function groupByCallback(
        array $array,
        \Closure $handler,
        int $flags = self::GROUP_TYPE_ARRAY
    ): array {
        $results = [];
        $hasArray = [];

        $type = match (true) {
            (bool) ($flags & static::GROUP_TYPE_KEY_BY) => static::GROUP_TYPE_KEY_BY,
            (bool) ($flags & static::GROUP_TYPE_MIX) => static::GROUP_TYPE_MIX,
            default => static::GROUP_TYPE_ARRAY
        };

        $isOverride = $flags & static::SAME_KEY_OVERRIDE;

        foreach ($array as $index => $value) {
            // List value
            $result = $handler($value, $index);

            if ($result instanceof \Generator) {
                $value = $result->current();
                $result = $result->key();
            }

            if ($result === null || $result === false) {
                continue;
            }

            $resultKey = TypeCast::toString($result);
            $resultValue = $value;

            // First set value if not exists.
            if (!isset($results[$resultKey])) {
                // Force first element in array.
                if ($type === static::GROUP_TYPE_ARRAY) {
                    $results[$resultKey] = [$resultValue];
                    $hasArray[$resultKey] = true;
                } else {
                    // Keep first element single.
                    $results[$resultKey] = $resultValue;
                }
            } elseif ($type === static::GROUP_TYPE_MIX && empty($hasArray[$resultKey])) {
                // If first element is single, now add second element as an array.
                $results[$resultKey] = [
                    $results[$resultKey],
                    $resultValue,
                ];

                $hasArray[$resultKey] = true;
            } elseif ($type === static::GROUP_TYPE_KEY_BY) {
                if (!array_key_exists($resultKey, $results) || $isOverride) {
                    $results[$resultKey] = $resultValue;
                }
            } else {
                // Now always push results elements.
                $results[$resultKey][] = $resultValue;
            }
        }

        unset($hasArray);

        return $results;
    }

    /**
     * mapWithKeys
     *
     * @param  iterable  $array
     * @param  callable  $handler
     * @param  int       $type
     *
     * @return  array
     *
     * @since  3.5.12
     */
    public static function mapWithKeys(iterable $array, callable $handler, int $type = self::GROUP_TYPE_KEY_BY): array
    {
        $results = [];

        foreach ($array as $k => $v) {
            $r = $handler($v, $k);

            if ($r instanceof \Generator) {
                $r = [$r->key() => $r->current()];
            }

            TypeAssert::assert(
                is_array($r),
                'Return value of {caller} should be array, got %s',
                $r
            );

            foreach ($r as $resultKey => $resultValue) {
                // First set value if not exists.
                if (!isset($results[$resultKey])) {
                    // Force first element in array.
                    if ($type === static::GROUP_TYPE_ARRAY) {
                        $results[$resultKey] = [$resultValue];
                        $hasArray[$resultKey] = true;
                    } else {
                        // Keep first element single.
                        $results[$resultKey] = $resultValue;
                    }
                } elseif ($type === static::GROUP_TYPE_MIX && empty($hasArray[$resultKey])) {
                    // If first element is single, now add second element as an array.
                    $results[$resultKey] = [
                        $results[$resultKey],
                        $resultValue,
                    ];

                    $hasArray[$resultKey] = true;
                } elseif ($type === static::GROUP_TYPE_KEY_BY) {
                    $results[$resultKey] = $resultValue;
                } else {
                    // Now always push results elements.
                    $results[$resultKey][] = $resultValue;
                }
            }
        }

        return $results;
    }

    /**
     * crossJoin
     *
     * @param  mixed  ...$args
     *
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function crossJoin(...$args): array
    {
        $results = [[]];

        foreach ($args as $i => $arg) {
            $append = [];

            foreach ($results as $value) {
                foreach ($arg as $item) {
                    $value[$i] = $item;
                    $append[] = $value;
                }
            }

            $results = $append;
        }

        return $results;
    }
}
