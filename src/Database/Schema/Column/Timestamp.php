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
use Windwalker\Query\ExpressionWrapper;

/**
 * The TIMESTAMP class.
 *
 * @since  2.0
 */
class Timestamp extends Column
{
    /**
     * Class init.
     *
     * @param string $name
     * @param bool   $allowNull
     * @param string $default
     * @param string $comment
     * @param array  $options
     */
    public function __construct($name = null, $allowNull = false, $default = 'current_timestamp', $comment = '', $options = [])
    {
        if (stripos($default, 'current_timestamp') === 0) {
            $default = new ExpressionWrapper($default);
        }

        parent::__construct($name, DataType::TIMESTAMP, true, $allowNull, $default, $comment, $options);
    }

    /**
     * autoUpdate
     *
     * @param string $value
     *
     * @return  $this
     *
     * @since  3.4
     */
    public function autoUpdate($value = 'CURRENT_TIMESTAMP')
    {
        $this->suffix = ' ON UPDATE ' . $value;

        return $this;
    }

    /**
     * setOptions
     *
     * @param array $options
     *
     * @return  static
     */
    public function setOptions(array $options)
    {
        if (isset($options['on_update'])) {
            $this->autoUpdate($options['on_update']);
        } elseif (isset($options['auto_update'])) {
            $this->autoUpdate();
        }

        return parent::setOptions($options);
    }
}
