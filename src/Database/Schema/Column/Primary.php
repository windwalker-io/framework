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
 * The Primary class.
 *
 * @since  2.0
 */
class Primary extends Column
{
    /**
     * Class init.
     *
     * @param string $name
     * @param string $comment
     * @param array  $options
     */
    public function __construct($name = null, $comment = '', $options = [])
    {
        $options['primary'] = true;

        parent::__construct($name, DataType::INTEGER, Column::UNSIGNED, Column::NOT_NULL, false, $comment, $options);
    }
}
