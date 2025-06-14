<?php

declare(strict_types=1);

namespace Windwalker\Query;

use Windwalker\Query\Clause\Clause;
use Windwalker\Query\Clause\ExprClause;
use Windwalker\Query\Clause\QuoteNameClause;
use Windwalker\Query\Clause\ValueClause;
use Windwalker\Query\Wrapper\UuidBinWrapper;
use Windwalker\Query\Wrapper\UuidWrapper;

if (!function_exists(__NAMESPACE__ . '\clause')) {
    /**
     * clause
     *
     * @param  string        $name
     * @param  array|string  $elements
     * @param  string        $glue
     *
     * @return  Clause
     */
    function clause(string $name = '', $elements = [], string $glue = ' '): Clause
    {
        return new Clause($name, $elements, $glue);
    }
}

if (!function_exists(__NAMESPACE__ . '\val')) {
    /**
     * val
     *
     * @param  mixed  $value
     *
     * @return  ValueClause|array
     */
    function val(mixed $value): ValueClause|array
    {
        if ($value instanceof ValueClause) {
            return $value;
        }

        if (is_array($value)) {
            return array_map(fn($v) => new ValueClause($v), $value);
        }

        return new ValueClause($value);
    }
}

if (!function_exists(__NAMESPACE__ . '\qn')) {
    /**
     * qn
     *
     * @param  mixed       $value
     * @param  Query|null  $query
     *
     * @return  QuoteNameClause|array
     */
    function qn(mixed $value, ?Query $query = null): QuoteNameClause|array
    {
        if ($value instanceof QuoteNameClause) {
            return $value;
        }

        if (is_array($value)) {
            return array_map(fn($v) => qn($v, $query), $value);
        }

        return new QuoteNameClause($value, $query);
    }
}

if (!function_exists(__NAMESPACE__ . '\expr')) {
    /**
     * clause
     *
     * @param  string  $name
     * @param  array   $elements
     *
     * @return  ExprClause
     */
    function expr(string $name = '', ...$elements): ExprClause
    {
        return new ExprClause($name, ...$elements);
    }
}

if (!function_exists(__NAMESPACE__ . '\uuid2bin')) {
    /**
     * @param  string  $value
     *
     * @return UuidBinWrapper|array
     */
    function uuid2bin(mixed $value): UuidBinWrapper|array
    {
        if (is_array($value)) {
            return array_map(fn($v) => uuid2bin($v), $value);
        }

        return new UuidBinWrapper($value);
    }
}

if (!function_exists(__NAMESPACE__ . '\try_uuid2bin')) {
    /**
     * @param  string  $value
     *
     * @return UuidBinWrapper|array|null
     */
    function try_uuid2bin(mixed $value): UuidBinWrapper|array|null
    {
        if (is_iterable($value)) {
            return array_map(fn($v) => try_uuid2bin($v), iterator_to_array($value));
        }

        if ($value === null || $value === '' || $value === '0x') {
            return null;
        }

        return new UuidBinWrapper($value);
    }
}
