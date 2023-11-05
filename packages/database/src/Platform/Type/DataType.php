<?php

declare(strict_types=1);

namespace Windwalker\Database\Platform\Type;

use Windwalker\Database\Schema\Ddl\Column;
use Windwalker\Utilities\TypeCast;

/**
 * The ColumnType class.
 *
 * The types data were referenced from:
 * https://docs.google.com/document/d/168GnMgXb8afOby1n9iLQXzu-PWujs-HxTv5YbEvmu-4/edit
 *
 * @since  2.0
 */
class DataType
{
    // BOOLEAN
    public const BOOLEAN = 'boolean';

    // CHARACTER
    public const CHAR = 'char';

    public const VARCHAR = 'varchar';

    // BIT
    public const BIT = 'bit';

    public const BIT_VARYING = 'bit varying';

    // EXACT NUMERIC
    public const BIGINT = 'bigint';

    public const INTEGER = 'integer';

    public const SMALLINT = 'smallint';

    public const DECIMAL = 'decimal';

    public const NUMERIC = 'numeric';

    // APPROXIMATE NUMERIC
    public const FLOAT = 'float';

    public const REAL = 'real';

    public const DOUBLE = 'double';

    // DATETIME
    public const DATE = 'date';

    public const TIME = 'time';

    public const TIMESTAMP = 'timestamp';

    // INTERVAL
    public const INTERVAL = 'interval';

    // LARGE OBJECTS
    public const CHARACTER = 'character';

    public const LARGE = 'large';

    public const OBJECT_BINARY = 'objectbinary';

    public const LARGE_OBJECT = 'large object';

    // Not SQL92 types but common
    public const TEXT = 'text';

    public const LONGTEXT = 'longtext';

    public const TINYINT = 'tinyint';

    public const DATETIME = 'datetime';

    /**
     * Property typeMapping.
     *
     * @var  array
     */
    protected static array $typeMapping = [];

    /**
     * "Default Length", "Default Value", "PHP Type"
     *
     * @var  array
     */
    public static array $typeDefinitions = [
        self::BOOLEAN => [1, 0, 'bool'],

        self::CHAR => [255, '', 'string'],
        self::CHARACTER => [255, '', 'string'],
        self::VARCHAR => [255, '', 'string'],
        self::TEXT => [null, '', 'string'],
        self::LONGTEXT => [null, '', 'string'],

        self::BIT => [1, 0, 'int'],
        self::BIT_VARYING => [1, 0, 'int'],

        self::BIGINT => [null, 0, 'int'],
        self::INTEGER => [null, 0, 'int'],
        self::SMALLINT => [null, 0, 'int'],
        self::TINYINT => [null, 0, 'int'],
        self::NUMERIC => [null, 0, 'int'],

        self::DECIMAL => ['10,2', 0, 'float'],
        self::FLOAT => ['10,2', 0, 'float'],
        self::REAL => ['10,2', 0, 'float'],
        self::DOUBLE => ['10,2', 0, 'float'],

        self::DATE => [null, '0000-00-00', 'string'],
        self::TIME => [null, '00:00:00', 'string'],
        self::TIMESTAMP => [null, 1, 'string'],
        self::DATETIME => [null, '0000-00-00 00:00:00', 'string'],
    ];

    /**
     * Property noLength.
     *
     * @var  array
     */
    protected static array $noLength = [];

    /**
     * Property instances.
     *
     * @var  static[]
     */
    protected static array $instances = [];

    /**
     * getLength
     *
     * @param  string  $type
     *
     * @return int|string
     */
    public static function getLength(string $type): int|string|null
    {
        return static::getDefinition($type, 0);
    }

    /**
     * getDefaultValue
     *
     * @param  string  $type
     *
     * @return mixed
     */
    public static function getDefaultValue(string $type): mixed
    {
        return static::getDefinition($type, 1);
    }

    /**
     * getPhpType
     *
     * @param  string  $type
     *
     * @return  string
     */
    public static function getPhpType(string $type): string
    {
        return static::getDefinition($type, 2) ?: 'string';
    }

    public static function castForSave(mixed $value, Column $column): mixed
    {
        return TypeCast::try(
            $value,
            static::getPhpType($column->getDataType()),
        );
    }

    protected static function getDefinition(string $type, ?int $key = null)
    {
        $type = strtolower($type);

        if (array_key_exists($type, static::$typeDefinitions)) {
            return static::$typeDefinitions[$type][$key];
        }

        if (array_key_exists($type, self::$typeDefinitions)) {
            return self::$typeDefinitions[$type][$key];
        }

        return null;
    }

    public static function getAvailableType(string $type): string
    {
        $type = strtolower($type);

        return static::$typeMapping[$type] ?? $type;
    }

    public static function isNoLength(string $type): bool
    {
        $type = strtolower($type);

        return in_array($type, static::$noLength, true);
    }

    /**
     * parseTypeName
     *
     * @param  string  $type
     *
     * @return  string
     *
     * @since  3.5.5
     */
    public static function parseTypeName(string $type): string
    {
        $parsed = explode(' ', $type)[0] ?? '';

        return explode('(', $parsed)[0] ?? '';
    }

    /**
     * Extract data type to [type, precision, scale].
     *
     * Example:
     * - datetime -> [datetime, '', '']
     * - int(11) -> [int, 11, '']
     * - decimal(20,6) -> [decimal, 20, 6]
     *
     * @param  string  $type
     *
     * @return  array
     */
    public static function extract(string $type): array
    {
        preg_match(
            '/(\w+)\(*(\w*)[,\s]*(\d*)\)*/',
            $type,
            $matches
        );

        array_shift($matches);

        return $matches;
    }
}
