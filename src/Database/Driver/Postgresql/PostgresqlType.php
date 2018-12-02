<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Database\Driver\Postgresql;

use Windwalker\Database\Driver\Mysql\MysqlType;
use Windwalker\Database\Schema\DataType;

/**
 * The PostgresqlType class.
 *
 * @since  2.1
 */
class PostgresqlType extends DataType
{
    const INTEGER = 'integer';

    const BOOLEAN = 'bool';

    const SERIAL = 'serial';

    const REAL = 'real';

    /**
     * Property typeMapping.
     *
     * @see  https://en.wikibooks.org/wiki/Converting_MySQL_to_PostgreSQL
     *
     * @var  array
     */
    protected static $typeMapping = [
        DataType::TINYINT => self::SMALLINT,
        DataType::DATETIME => self::TIMESTAMP,
        'tinytext' => self::TEXT,
        'mediumtext' => self::TEXT,
        DataType::LONGTEXT => self::TEXT,
        // MysqlType::ENUM => self::VARCHAR, // Postgres support ENUM after 8.3
        MysqlType::SET => self::TEXT,
        MysqlType::FLOAT => self::REAL,
    ];

    /**
     * "Default Length", "Default Value", "PHP Type"
     *
     * @var  array
     */
    public static $typeDefinitions = [
        self::BOOLEAN => [1, 0, 'bool'],
        self::SERIAL => [null, 0, 'int'],
        self::INTEGER => [null, 0, 'int'],
        self::SMALLINT => [null, 0, 'int'],
        self::REAL => [null, 0, 'float'],
        self::TIMESTAMP => [null, '1970-01-01 00:00:00', 'string'],
        self::INTERVAL => [16, 0, 'string'],
        self::DATETIME => [null, '1970-01-01 00:00:00', 'string'],
    ];

    /**
     * Property noLength.
     *
     * @var  array
     */
    protected static $noLength = [
        self::INTEGER,
        self::SMALLINT,
        self::SERIAL,
        self::REAL,
    ];

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

        return in_array($type, static::$noLength);
    }
}
