<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

namespace Windwalker\Database\Schema\Column;

use Windwalker\Database\Schema\Column;
use Windwalker\Database\Schema\DataType;

/**
 * The Varchar class.
 *
 * @since  2.0
 */
class Char extends Column
{
    /**
     * Class init.
     *
     * @param string $name
     * @param int    $length
     * @param bool   $allowNull
     * @param string $default
     * @param string $comment
     * @param array  $options
     */
    public function __construct(
        $name = null,
        $length = null,
        $allowNull = false,
        $default = '',
        $comment = '',
        $options = []
    ) {
        $options['length'] = $length;

        parent::__construct($name, DataType::CHAR, Column::SIGNED, $allowNull, $default, $comment, $options);
    }
}
