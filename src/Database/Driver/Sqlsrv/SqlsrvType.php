<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Database\Driver\Sqlsrv;

use Windwalker\Database\Schema\DataType;

/**
 * The SqlsrvType class.
 *
 * @see https://docs.microsoft.com/zh-tw/sql/ssma/mysql/project-settings-type-mapping-mysqltosql?view=sql-server-2017
 * @see https://support.dbconvert.com/hc/en-us/articles/202952551-Mapping-MySQL-and-SQL-Server-Data-Types
 *
 * @since  2.0
 */
class SqlsrvType extends DataType
{
    // BOOLEAN
    const BOOLEAN = 'bit';

    // CHARACTER
    const NCHAR = 'nchar';

    const NVARCHAR = 'nvarchar';

    const NTEXT = 'ntext';

    // EXACT NUMERIC
    const INTEGER = 'int';

    const DATETIME2 = 'datetime2';

    /**
     * "Length", "Default", "PHP Type"
     *
     * @var  array
     */
    public static $typeDefinitions = [
        self::INTEGER => [null, 0, 'int'],
        self::BIGINT => [null, 0, 'int'],
        self::SMALLINT => [null, 0, 'int'],
        self::TINYINT => [null, 0, 'int'],
        self::FLOAT => [24, 0, 'float'],
        self::DOUBLE => [53, 0, 'float'],
        self::REAL => [53, 0, 'float'],
        self::NCHAR => [255, 0, 'string'],
        self::NVARCHAR => [255, 0, 'string'],
        self::DATETIME2 => [null, '1900-01-01 00:00:00', 'string']
    ];

    /**
     * Property typeMapping.
     *
     * @var  array
     */
    protected static $typeMapping = [
        DataType::INTEGER => 'int',
        DataType::TINYINT => self::SMALLINT,
        DataType::BOOLEAN => self::BIT,
        DataType::VARCHAR => self::NVARCHAR,
        DataType::CHAR => self::NCHAR,
        DataType::TEXT => self::NTEXT,
        DataType::LONGTEXT => self::NTEXT,
        DataType::DATETIME => self::DATETIME2,
        DataType::TIMESTAMP => self::DATETIME2,
    ];

    /**
     * Property noLength.
     *
     * @var  array
     */
    protected static $noLength = [
        self::INTEGER,
        self::SMALLINT,
        self::NTEXT,
    ];

    /**
     * getLength
     *
     * @param   string $type
     *
     * @return  integer
     */
    public static function getLength($type)
    {
        return parent::getLength($type);
    }
}
