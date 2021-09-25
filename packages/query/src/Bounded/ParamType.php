<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Query\Bounded;

/**
 * The ParamType class.
 */
class ParamType
{
    public const STRING = 'string';

    public const INT = 'int';

    public const FLOAT = 'float';

    public const BLOB = 'blob';

    public const BOOL = 'bool';

    public const NULL = 'null';

    private const MYSQLI_MAPS = [
        self::STRING => 's',
        self::INT => 'i',
        self::FLOAT => 'd',
        self::BLOB => 'b',
        self::BOOL => 'i',
        self::NULL => 'i',
    ];

    /**
     * convertToPDO
     *
     * @param  string|null  $type
     *
     * @return  ?int
     */
    public static function convertToPDO(?string $type): ?int
    {
        return match ($type) {
            self::STRING => \PDO::PARAM_STR,
            self::INT => \PDO::PARAM_INT,
            self::FLOAT => \PDO::PARAM_STR,
            self::BLOB => \PDO::PARAM_LOB,
            self::BOOL => \PDO::PARAM_BOOL,
            self::NULL => \PDO::PARAM_NULL,
            default => $type
        };
    }

    /**
     * convertToMysqli
     *
     * @param  string  $type
     *
     * @return  string
     */
    public static function convertToMysqli(string $type): string
    {
        return match ($type) {
            self::STRING => 's',
            self::INT, self::BOOL, self::NULL => 'i',
            self::FLOAT => 'd',
            self::BLOB => 'b',
            default => $type
        };
    }

    /**
     * guessType
     *
     * @param  mixed  $value
     *
     * @return  string
     */
    public static function guessType(mixed $value): string
    {
        if (is_string($value) && is_numeric($value)) {
            $val = trim($value, '0.');

            if ($val !== $value || $value >= PHP_INT_MAX) {
                return static::STRING;
            }

            if (str_contains($val, '.')) {
                return static::FLOAT;
            }

            return static::INT;
        }

        return match(true) {
            is_int($value) => static::INT,
            is_float($value) => static::FLOAT,
            $value === null => static::NULL,
            default => static::STRING
        };
    }
}
