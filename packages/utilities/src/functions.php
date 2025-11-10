<?php

declare(strict_types=1);

namespace {

    use JetBrains\PhpStorm\Pure;
    use Windwalker\Utilities\Arr;

    if (!function_exists('show')) {
        /**
         * Dump Array or Object as tree node. If send multiple params in this method, this function will batch print it.
         *
         * @param  mixed  $args  Array or Object to dump.
         *
         * @return  void
         * @since   2.0
         *
         */
        function show(...$args)
        {
            Arr::show(...$args);
        }
    }

    if (!function_exists('is_stringable')) {
        /**
         * is_stringable
         *
         * @param  mixed  $var
         *
         * @return  bool
         *
         * @since  3.5
         */
        #[Pure]
        function is_stringable(
            mixed $var
        ): bool {
            if (is_array($var)) {
                return false;
            }

            if (is_object($var) && !$var instanceof Stringable && !method_exists($var, '__toString')) {
                return false;
            }

            if (is_resource($var)) {
                return false;
            }

            return true;
        }
    }

    if (!function_exists('is_json')) {
        /**
         * @param  mixed  $string
         *
         * @return  bool
         *
         * @since  3.5.8
         */
        function is_json(mixed $string): bool
        {
            if (!is_string($string)) {
                return false;
            }

            return json_validate($string);
        }
    }

    if (!function_exists('is_scalar_or_null')) {
        /**
         * @param  mixed  $value
         *
         * @return  bool
         */
        function is_scalar_or_null(mixed $value): bool
        {
            return is_scalar($value) || $value === null;
        }
    }

    include_once __DIR__ . '/serializer.php';
}

namespace Windwalker {

    use Closure;
    use Exception;
    use JetBrains\PhpStorm\Pure;
    use MyCLabs\Enum\Enum;
    use Traversable;
    use WeakReference;
    use Windwalker\Attributes\AttributesAccessor;
    use Windwalker\Utilities\Classes\TraitHelper;
    use Windwalker\Utilities\Compare\CompareHelper;
    use Windwalker\Utilities\Compare\WhereWrapper;
    use Windwalker\Utilities\Piping;
    use Windwalker\Utilities\Proxy\CachedCallable;
    use Windwalker\Utilities\Proxy\CallableProxy;
    use Windwalker\Utilities\Proxy\DisposableCallable;
    use Windwalker\Utilities\Serial;
    use Windwalker\Utilities\TypeCast;
    use Windwalker\Utilities\Wrapper\DepthWrapper;
    use Windwalker\Utilities\Wrapper\RawWrapper;
    use Windwalker\Utilities\Wrapper\ValueReference;
    use Windwalker\Utilities\Wrapper\WrapperInterface;

    include_once __DIR__ . '/functions_lazy.php';

    if (!function_exists('\Windwalker\nope')) {
        /**
         * nope
         *
         * @return  Closure
         */
        #[Pure]
        function nope(): Closure
        {
            return function (mixed $v = null, mixed ...$args) {
                return $v;
            };
        }
    }

    if (!function_exists('\Windwalker\tap')) {
        /**
         * Do some operation after value get.
         *
         * @param  mixed     $value
         * @param  callable  $callable
         *
         * @return  mixed
         *
         * @since  3.5.1
         */
        function tap(mixed $value, callable $callable): mixed
        {
            $callable($value);

            return $value;
        }
    }

    if (!function_exists('\Windwalker\pipe')) {
        /**
         * Do some operation after value get.
         *
         * @param  mixed     $value
         * @param  callable  $callable
         *
         * @return  mixed
         */
        function pipe(mixed $value, callable $callable): mixed
        {
            return $callable($value);
        }
    }

    if (!function_exists('\Windwalker\piping')) {
        function piping(mixed $value): Piping
        {
            return new Piping($value);
        }
    }

    if (!function_exists('\Windwalker\count')) {
        /**
         * Count NULL as 0 to workaround some code before php7.2
         *
         * @param  mixed  $value
         * @param  int    $mode
         *
         * @return  int
         *
         * @since  3.5.13
         */
        function count(mixed $value, int $mode = COUNT_NORMAL): int
        {
            if ($value instanceof Traversable) {
                return iterator_count($value);
            }

            return $value !== null ? \count($value, $mode) : 0;
        }
    }

    if (!function_exists('\Windwalker\uid')) {
        /**
         * Generate an unique ID.
         *
         * @param  string  $prefix    Prefix of this ID.
         * @param  bool    $timebase  Make this ID time-based that can be sortable.
         *
         * @return  string
         *
         * @throws Exception
         */
        function uid(string $prefix = '', bool $timebase = false): string
        {
            if ($timebase) {
                static $last = null;

                do {
                    $microtime = \microtime();
                } while ($microtime === $last);

                $last = $microtime;

                [$b, $a] = explode(' ', $microtime, 2);

                $c = $a . substr($b, 2);

                return $prefix . base_convert($c, 10, 12) . bin2hex(random_bytes(4));
            }

            return $prefix . bin2hex(random_bytes(12));
        }
    }

    if (!function_exists('\Windwalker\tid')) {
        /**
         * Generate a time-based unique ID.
         *
         * @param  string  $prefix  Prefix of this ID.
         *
         * @return  string
         *
         * @throws Exception
         */
        function tid(string $prefix = ''): string
        {
            return uid($prefix, true);
        }
    }

    if (!function_exists('\Windwalker\serial')) {
        /**
         * To get a auto increment serial number. The sequence can be string name or and object.
         *
         * @param  string|object  $sequence
         *
         * @return  int
         */
        function serial(string|object $sequence = 'default'): int
        {
            return Serial::get($sequence);
        }
    }

    if (!function_exists('\Windwalker\iterator_keys')) {
        /**
         * iterator_keys
         *
         * @param  Traversable  $iterable
         *
         * @return  array
         *
         * @since  __DEPLOY_VERSION__
         */
        #[Pure]
        function iterator_keys(
            Traversable $iterable
        ): array {
            return array_keys(iterator_to_array($iterable));
        }
    }

    if (!function_exists('\Windwalker\where')) {
        /**
         * where
         *
         * @param  mixed   $var1
         * @param  string  $operator
         * @param  mixed   $var2
         * @param  bool    $strict
         *
         * @return  WhereWrapper
         *
         * @since  __DEPLOY_VERSION__
         */
        #[Pure]
        function where(
            mixed $var1,
            string $operator,
            mixed $var2,
            bool $strict = false
        ): WhereWrapper {
            return new WhereWrapper($var1, $operator, $var2, $strict);
        }
    }

    if (!function_exists('\Windwalker\value')) {
        /**
         * value
         *
         * @param  mixed|Closure  $value
         * @param  mixed          ...$args
         *
         * @return  mixed
         *
         * @since  __DEPLOY_VERSION__
         */
        function value(mixed $value, mixed ...$args): mixed
        {
            if ($value instanceof \UnitEnum) {
                return unwrap_enum($value);
            }

            if ($value instanceof Enum) {
                return $value->getValue();
            }

            if ($value instanceof WeakReference) {
                return $value->get();
            }

            if ($value instanceof WrapperInterface) {
                return $value(...$args);
            }

            return ($value instanceof Closure || $value instanceof CallableProxy)
                ? $value(...$args)
                : $value;
        }
    }

    if (!function_exists('\Windwalker\unwrap_enum')) {
        /**
         * value
         *
         * @param  mixed|Closure  $value
         *
         * @return  mixed
         */
        function unwrap_enum(mixed $value): mixed
        {
            if ($value instanceof \UnitEnum) {
                return TypeCast::extractEnum($value);
            }

            return $value;
        }
    }

    if (!function_exists('\Windwalker\unwrap')) {
        /**
         * unwrap
         *
         * @param  mixed  $value
         * @param  mixed  ...$args
         *
         * @return  mixed
         */
        function unwrap(mixed $value, ...$args): mixed
        {
            if ($value instanceof WrapperInterface) {
                return $value(...$args);
            }

            return $value;
        }
    }

    if (!function_exists('\Windwalker\raw')) {
        /**
         * raw
         *
         * @param  mixed  $value
         *
         * @return  RawWrapper
         */
        #[Pure]
        function raw(
            mixed $value
        ): RawWrapper {
            return new RawWrapper($value);
        }
    }

    if (!function_exists('\Windwalker\depth')) {
        #[Pure]
        function depth(int $depth): DepthWrapper
        {
            return new DepthWrapper($depth);
        }
    }

    if (!function_exists('\Windwalker\ref')) {
        /**
         * ref
         *
         * @param  string       $path
         * @param  string|null  $delimiter
         *
         * @return  ValueReference
         *
         * @since  __DEPLOY_VERSION__
         */
        #[Pure]
        function ref(
            string $path,
            ?string $delimiter = '.'
        ): ValueReference {
            return new ValueReference($path, $delimiter);
        }
    }

    if (!function_exists('\Windwalker\disposable')) {
        /**
         * dispose
         *
         * @param  callable  $callable
         *
         * @return  DisposableCallable
         */
        function disposable(callable $callable): DisposableCallable
        {
            return new DisposableCallable($callable);
        }
    }

    if (!function_exists('\Windwalker\cachable')) {
        /**
         * cachable
         *
         * @param  callable  $callable
         *
         * @return  CachedCallable
         */
        function cachable(callable $callable): CachedCallable
        {
            return new CachedCallable($callable);
        }
    }

    if (!function_exists('\Windwalker\value_compare')) {
        /**
         * value_compare
         *
         * @param  mixed        $a
         * @param  mixed        $b
         * @param  string|null  $operator
         *
         * @return  int|bool
         */
        function value_compare(mixed $a, mixed $b, ?string $operator = null): int|bool
        {
            return CompareHelper::compare($a, $b, $operator);
        }
    }

    if (!function_exists('\Windwalker\clamp')) {
        function clamp(int|float $num, int|float|null $min, int|float|null $max): int|float
        {
            if ($min !== null && $max !== null && $max < $min) {
                throw new \InvalidArgumentException(
                    __FUNCTION__ . '(): Argument #2 ($min) cannot be greater than Argument #3 ($max)'
                );
            }

            if ($min !== null && $num < $min) {
                $num = $min;
            }

            if ($max !== null && $num > $max) {
                $num = $max;
            }

            return $num;
        }
    }

    if (!function_exists('\Windwalker\has_attributes')) {
        function has_attributes(mixed $ref, string $attr, bool $instanceof = false): bool
        {
            $flags = $instanceof ? \ReflectionAttribute::IS_INSTANCEOF : 0;

            if ($ref instanceof \Reflector) {
                return $ref->getAttributes($attr, $flags) !== [];
            }

            return AttributesAccessor::getAttributesFromAny($ref, $attr, $flags) !== [];
        }
    }

    if (!function_exists('\Windwalker\trait_uses')) {
        function trait_uses(string|object $object, bool $autoload = true): array
        {
            return TraitHelper::classUsesRecursive($object, $autoload);
        }
    }

    if (!function_exists('\Windwalker\has_used_trait')) {
        function has_used_trait(string|object $object, string $trait, bool $autoload = true): bool
        {
            return TraitHelper::uses($object, $trait, $autoload);
        }
    }

    if (!function_exists('\Windwalker\get_object_props')) {
        /**
         * @param  object    $object
         * @param  int|null  $filter
         *
         * @return  array<\ReflectionProperty>
         */
        function get_object_props(
            object $object,
            ?int $filter = null
        ): array {
            $filter ??= \ReflectionProperty::IS_PUBLIC;

            $values = [];

            $ref = new \ReflectionObject($object);
            $props = $ref->getProperties($filter);

            foreach ($props as $prop) {
                $name = $prop->getName();

                if (!$prop->isInitialized($object)) {
                    continue;
                }

                if (!($filter & \ReflectionProperty::IS_VIRTUAL) && $prop->isVirtual()) {
                    continue;
                }

                $values[$name] = $prop;
            }

            return $values;
        }
    }

    if (!function_exists('\Windwalker\get_object_values')) {
        function get_object_values(
            object $object,
            ?int $filter = null
        ): array {
            return array_map(
                static fn (\ReflectionProperty $prop) => $prop->getValue($object),
                get_object_props(
                    $object,
                    $filter
                )
            );
        }
    }
}
