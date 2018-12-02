<?php
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
 * The Bigint class.
 *
 * @since  2.0
 */
class Bigint extends Column
{
    /**
     * Class init.
     *
     * @param string $name
     * @param int    $length
     * @param bool   $signed
     * @param bool   $allowNull
     * @param string $default
     * @param string $comment
     * @param array  $options
     */
    public function __construct(
        $name = null,
        $length = null,
        $signed = false,
        $allowNull = false,
        $default = 0,
        $comment = '',
        $options = []
    ) {
        $options['length'] = $length;

        parent::__construct($name, DataType::BIGINT, $signed, $allowNull, $default, $comment, $options);
    }
}
