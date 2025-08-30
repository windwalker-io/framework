<?php

declare(strict_types=1);

namespace Windwalker {
    if (!function_exists('\Windwalker\unwrap_lazy_object')) {
        /**
         * @template  T of object
         *
         * @param  T  $object
         *
         * @return  T
         */
        function unwrap_lazy_object(object $object): object
        {
            return new \ReflectionClass($object)->initializeLazyObject($object);
        }
    }

    if (!function_exists('\Windwalker\unwrap_object_id')) {
        function unwrap_object_id(object $object): int
        {
            return spl_object_id(unwrap_lazy_object($object));
        }
    }

    if (!function_exists('\Windwalker\unwrap_object_hash')) {
        function unwrap_object_hash(object $object): string
        {
            return spl_object_hash(unwrap_lazy_object($object));
        }
    }

    if (!function_exists('\Windwalker\object_is_proxy')) {
        function object_is_proxy(object $object): bool
        {
            return object_is_uninitialized_lazy($object) || unwrap_object_id($object) !== spl_object_id($object);
        }
    }

    if (!function_exists('\Windwalker\object_is_uninitialized_lazy')) {
        function object_is_uninitialized_lazy(object $object): bool
        {
            return new \ReflectionClass($object)->isUninitializedLazyObject($object);
        }
    }
}
