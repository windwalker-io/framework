<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Database\Schema;

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
    const BOOLEAN = 'boolean';

    // CHARACTER
    const CHAR = 'char';

    const VARCHAR = 'varchar';

    // BIT
    const BIT = 'bit';

    const BIT_VARYING = 'bit varying';

    // EXACT NUMERIC
    const BIGINT = 'bigint';

    const INTEGER = 'integer';

    const SMALLINT = 'smallint';

    const DECIMAL = 'decimal';

    const NUMERIC = 'numeric';

    // APPROXIMATE NUMERIC
    const FLOAT = 'float';

    const REAL = 'real';

    const DOUBLE = 'double';

    // DATETIME
    const DATE = 'date';

    const TIME = 'time';

    const TIMESTAMP = 'timestamp';

    // INTERVAL
    const INTERVAL = 'interval';

    // LARGE OBJECTS
    const CHARACTER = 'character';

    const LARGE = 'large';

    const OBJECT_BINARY = 'objectbinary';

    const LARGE_OBJECT = 'large object';

    // Not SQL92 types but common
    const TEXT = 'text';

    const LONGTEXT = 'longtext';

    const TINYINT = 'tinyint';

    const DATETIME = 'datetime';

    /**
     * Property typeMapping.
     *
     * @var  array
     */
    protected static $typeMapping = [];

    /**
     * "Default Length", "Default Value", "PHP Type"
     *
     * @var  array
     */
    public static $typeDefinitions = [
        self::BOOLEAN => [1, 0, 'bool'],

        self::CHAR => [255, '', 'string'],
        self::VARCHAR => [255, '', 'string'],
        self::TEXT => [null, '', 'string'],
        self::LONGTEXT => [null, '', 'string'],

        self::BIT => [1, 0, 'int'],
        self::BIT_VARYING => [1, 0, 'int'],

        self::BIGINT => [20, 0, 'int'],
        self::INTEGER => [11, 0, 'int'],
        self::SMALLINT => [6, 0, 'int'],
        self::TINYINT => [4, 0, 'int'],
        self::NUMERIC => [10, 0, 'int'],

        self::DECIMAL => ['10,2', 0, 'float'],
        self::FLOAT => ['10,2', 0, 'float'],
        self::REAL => ['10,2', 0, 'float'],
        self::DOUBLE => ['10,2', 0, 'float'],

        self::DATE => [null, '0000-00-00', 'string'],
        self::TIME => [null, '00:00:00', 'string'],
        self::TIMESTAMP => [null, '0', 'string'],
        self::DATETIME => [null, '0000-00-00 00:00:00', 'string'],
    ];

    /**
     * Property noLength.
     *
     * @var  array
     */
    protected static $noLength = [];

    /**
     * Property instances.
     *
     * @var  static[]
     */
    protected static $instances = [];

    /**
     * getInstance
     *
     * @param   string $driver
     *
     * @return  static
     */
    public static function getInstance($driver)
    {
        $driver = ucfirst($driver);

        if (!isset(static::$instances[$driver])) {
            $class = sprintf('Windwalker\Database\Driver\%s\%sType', $driver, $driver);

            static::$instances[$driver] = new $class();
        }

        return static::$instances[$driver];
    }

    /**
     * getLength
     *
     * @param   string $type
     *
     * @return  integer
     */
    public static function getLength($type)
    {
        return static::getProfile($type, 0);
    }

    /**
     * getDefaultValue
     *
     * @param   string $type
     *
     * @return  string
     */
    public static function getDefaultValue($type)
    {
        return static::getProfile($type, 1);
    }

    /**
     * getPhpType
     *
     * @param   string $type
     *
     * @return  string
     */
    public static function getPhpType($type)
    {
        return static::getProfile($type, 2) ?: 'string';
    }

    /**
     * getProfile
     *
     * @param string  $type
     * @param integer $key
     *
     * @return  string
     */
    protected static function getProfile($type, $key = null)
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

    /**
     * getType
     *
     * @param   string $type
     *
     * @return  string
     */
    public static function getType($type)
    {
        $type = strtolower($type);

        if (!isset(static::$typeMapping[$type])) {
            return $type;
        }

        return static::$typeMapping[$type];
    }

    /**
     * noLength
     *
     * @param   string $type
     *
     * @return  boolean
     */
    public static function noLength($type)
    {
        $type = strtolower($type);

        return in_array($type, static::$noLength, true);
    }
}
