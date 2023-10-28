<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Platform\Type;

/**
 * The PostgresqlType class.
 *
 * @since  2.1
 */
class PostgreSQLDataType extends DataType
{
    public const INTEGER = 'integer';

    public const BOOLEAN = 'bool';

    public const SERIAL = 'serial';

    public const REAL = 'real';

    /**
     * Property typeMapping.
     *
     * @see  https://en.wikibooks.org/wiki/Converting_MySQL_to_PostgreSQL
     *
     * @var  array
     */
    protected static array $typeMapping = [
        DataType::TINYINT => self::SMALLINT,
        DataType::DATETIME => self::TIMESTAMP,
        'tinytext' => self::TEXT,
        'mediumtext' => self::TEXT,
        DataType::LONGTEXT => self::TEXT,
        // MysqlType::ENUM => self::VARCHAR, // Postgres support ENUM after 8.3
        MySQLDataType::SET => self::TEXT,
        MySQLDataType::FLOAT => self::REAL,
    ];

    /**
     * "Default Length", "Default Value", "PHP Type"
     *
     * @var  array
     */
    public static array $typeDefinitions = [
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
    protected static array $noLength = [
        self::INTEGER,
        self::SMALLINT,
        self::SERIAL,
        self::REAL,
    ];
}
