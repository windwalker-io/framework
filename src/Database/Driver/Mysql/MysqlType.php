<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Database\Driver\Mysql;

use Windwalker\Database\Schema\DataType;

/**
 * The MysqlType class.
 *
 * @since  2.0
 */
class MysqlType extends DataType
{
    const INTEGER = 'int';

    const BOOLEAN = 'bool';

    const ENUM = 'enum';

    const SET = 'set';

    /**
     * Property types.
     *
     * @var  array
     */
    public static $defaultLengths = [
        self::INTEGER => 11,
        self::BIGINT => 20,
    ];

    /**
     * "Length", "Default", "PHP Type"
     *
     * @var  array
     */
    public static $typeDefinitions = [
        self::BOOLEAN => [1, 0, 'bool'],
        self::INTEGER => [11, 0, 'int'],
        self::BIGINT => [20, 0, 'int'],
        self::ENUM => [null, '', 'string'],
        self::SET => [null, '', 'string'],
        self::DATETIME => [null, '1000-01-01 00:00:00', 'string'],
    ];

    /**
     * Property typeMapping.
     *
     * @var  array
     */
    protected static $typeMapping = [
        DataType::INTEGER => 'int',
        DataType::BIT => self::TINYINT,
        DataType::BOOLEAN => self::BOOLEAN,
    ];
}
