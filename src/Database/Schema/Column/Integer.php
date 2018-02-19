<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

namespace Windwalker\Database\Schema\Column;

use Windwalker\Database\Schema\Column;
use Windwalker\Database\Schema\DataType;

/**
 * The Varchar class.
 *
 * @since  2.0
 */
class Integer extends Column
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

        parent::__construct($name, DataType::INTEGER, $signed, $allowNull, $default, $comment, $options);
    }
}
