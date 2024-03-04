<?php

declare(strict_types=1);

namespace Windwalker\Database\Platform\Type;

/**
 * The MysqlType class.
 *
 * @since  2.0
 */
class MySQLDataType extends DataType
{
    public const INTEGER = 'int';

    public const BOOLEAN = 'bool';

    public const ENUM = 'enum';

    public const SET = 'set';
    
    public const BINARY = 'binary';

    /**
     * Property types.
     *
     * @var  array
     */
    public static array $defaultLengths = [
        self::INTEGER => 11,
        self::BIGINT => 20,
    ];

    /**
     * "Length", "Default", "PHP Type"
     *
     * @var  array
     */
    public static array $typeDefinitions = [
        self::BOOLEAN => [null, 0, 'bool'],
        self::INTEGER => [11, 0, 'int'],
        self::BIGINT => [20, 0, 'int'],
        self::TINYINT => [4, 0, 'int'],
        self::ENUM => [null, '', 'string'],
        self::SET => [null, '', 'string'],
        self::DATETIME => [null, '1000-01-01 00:00:00', 'string'],
        self::TIMESTAMP => [null, '1970-01-01 12:00:01', 'int'],
        self::TEXT => [null, false, 'string'],
        self::LONGTEXT => [null, false, 'string'],
        self::BINARY => [16, '', 'string'],
    ];

    /**
     * Property typeMapping.
     *
     * @var  array
     */
    protected static array $typeMapping = [
        DataType::INTEGER => 'int',
        DataType::BIT => self::TINYINT,
        DataType::BOOLEAN => self::BOOLEAN,
    ];

    /**
     * Property noLength.
     *
     * @var  array
     */
    protected static array $noLength = [
        self::TEXT,
        self::LONGTEXT,
        self::DATE,
        self::DATETIME,
        self::TIMESTAMP,
    ];
}
