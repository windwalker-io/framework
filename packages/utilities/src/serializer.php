<?php

declare(strict_types=1);

namespace Windwalker;

use Closure;
use Laravel\SerializableClosure\SerializableClosure;
use LogicException;

if (!function_exists('\Windwalker\serialize')) {
    function serialize(mixed $data): string
    {
        if (!$data instanceof \Closure) {
            return \serialize($data);
        }

        if (!class_exists(SerializableClosure::class)) {
            throw new LogicException('Please install laravel/serializable-closure first');
        }

        return \serialize(new SerializableClosure($data));
    }
}

if (!function_exists('\Windwalker\unserialize')) {
    function unserialize(string $data, array $options = [])
    {
        if (!class_exists(SerializableClosure::class)) {
            throw new LogicException('Please install laravel/serializable-closure first');
        }

        $result = \unserialize($data, $options);

        if ($result instanceof SerializableClosure) {
            return $result->getClosure();
        }

        return $result;
    }
}

if (!function_exists('\Windwalker\closure')) {
    function closure(Closure $closure): SerializableClosure
    {
        if (!class_exists(SerializableClosure::class)) {
            throw new LogicException('Please install laravel/serializable-closure first');
        }

        return new SerializableClosure($closure);
    }
}
