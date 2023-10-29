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
 * The SQLiteDataType class.
 */
class SQLiteDataType extends DataType
{
    /**
     * Property types.
     *
     * @var  array
     */
    public static array $defaultLengths = [
    ];

    /**
     * "Length", "Default", "PHP Type"
     *
     * @var  array
     */
    public static array $typeDefinitions = [
        //
    ];

    /**
     * Property typeMapping.
     *
     * @var  array
     */
    protected static array $typeMapping = [
        'int' => 'integer',
        'bool' => 'smallint',
    ];
}
