<?php

declare(strict_types=1);

namespace Windwalker\Database\Platform\Type;

/**
 * The OracleDataType class.
 *
 * @see https://docs.oracle.com/en/database/oracle/oracle-database/23/sqlrf/Data-Types.html
 */
class OracleDataType extends DataType
{
    public const string VARCHAR2 = 'varchar2';

    public const string NVARCHAR2 = 'nvarchar2';

    public const string NUMBER = 'number';

    public const string NUMBER_INT = 'number(10)';

    public const string NUMBER_BIGINT = 'number(19)';

    public const string NUMBER_SMALLINT = 'number(5)';

    public const string NUMBER_TINYINT = 'number(3)';

    public const string NUMBER_BOOL = 'number(1)';

    public const string CLOB = 'clob';

    public const string NCLOB = 'nclob';

    public const string BINARY_FLOAT = 'binary_float';

    public const string BINARY_DOUBLE = 'binary_double';

    /**
     * "Length", "Default", "PHP Type"
     *
     * @var  array
     */
    public static array $typeDefinitions = [
        self::VARCHAR2 => [255, '', 'string'],
        self::NVARCHAR2 => [255, '', 'string'],
        self::CHAR => [255, '', 'string'],
        self::CLOB => [null, '', 'string'],
        self::NCLOB => [null, '', 'string'],
        self::NUMBER => ['10,2', 0, 'string'],
        self::NUMBER_INT => [null, 0, 'int'],
        self::NUMBER_BIGINT => [null, 0, 'int'],
        self::NUMBER_SMALLINT => [null, 0, 'int'],
        self::NUMBER_TINYINT => [null, 0, 'int'],
        self::NUMBER_BOOL => [null, 0, 'bool'],
        self::BINARY_FLOAT => [null, 0, 'float'],
        self::BINARY_DOUBLE => [null, 0, 'float'],
        self::DATE => [null, '1000-01-01', 'string'],
        self::TIMESTAMP => [null, '1000-01-01 00:00:00', 'string'],
    ];

    /**
     * @var  array
     */
    protected static array $typeMapping = [
        DataType::INTEGER => self::NUMBER_INT,
        'int' => self::NUMBER_INT,
        DataType::BIGINT => self::NUMBER_BIGINT,
        DataType::SMALLINT => self::NUMBER_SMALLINT,
        DataType::TINYINT => self::NUMBER_TINYINT,
        DataType::BOOLEAN => self::NUMBER_BOOL,
        'bool' => self::NUMBER_BOOL,
        DataType::DECIMAL => self::NUMBER,
        DataType::NUMERIC => self::NUMBER,
        DataType::FLOAT => self::BINARY_FLOAT,
        DataType::REAL => self::BINARY_FLOAT,
        DataType::DOUBLE => self::BINARY_DOUBLE,
        DataType::VARCHAR => self::VARCHAR2,
        DataType::TEXT => self::CLOB,
        DataType::LONGTEXT => self::CLOB,
        'json' => self::CLOB,
        DataType::TIME => self::VARCHAR2,
        DataType::DATETIME => self::TIMESTAMP,
    ];

    /**
     * @var  array
     */
    protected static array $noLength = [
        self::NUMBER_INT,
        self::NUMBER_BIGINT,
        self::NUMBER_SMALLINT,
        self::NUMBER_TINYINT,
        self::NUMBER_BOOL,
        self::CLOB,
        self::NCLOB,
        self::BINARY_FLOAT,
        self::BINARY_DOUBLE,
        self::DATE,
        self::TIMESTAMP,
    ];
}
